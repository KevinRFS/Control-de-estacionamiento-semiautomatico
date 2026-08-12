import os
import re
import time
import traceback

import cv2
from flask import Flask, jsonify, request
from paddleocr import PaddleOCR
from ultralytics import YOLO


# =====================================================
# CONFIGURACIÓN
# =====================================================

app = Flask(__name__)

BASE_DIR = os.path.dirname(
    os.path.abspath(__file__)
)

MODEL_PATH = os.path.join(
    BASE_DIR,
    "best.pt"
)

YOLO_CONFIDENCE = 0.50

MIN_PLATE_LENGTH = 5
MAX_PLATE_LENGTH = 8


# =====================================================
# CARGAR MODELOS UNA SOLA VEZ
# =====================================================

print("Cargando modelo YOLO...")

model = YOLO(MODEL_PATH)

print("Cargando PaddleOCR...")

ocr = PaddleOCR(
    use_angle_cls=True,
    lang="en",
    show_log=False
)

print("Modelos cargados correctamente.")


# =====================================================
# LIMPIAR TEXTO
# =====================================================

def clean_plate_text(text):
    cleaned = str(text).upper().strip()

    cleaned = cleaned.replace(
        "PARAGUAY",
        ""
    )

    cleaned = re.sub(
        r"[^A-Z0-9]",
        "",
        cleaned
    )

    return cleaned


# =====================================================
# RECONOCER MATRÍCULA
# =====================================================

def detect_plate(image_path):
    image = cv2.imread(image_path)

    if image is None:
        return ""

    results = model(
        image,
        stream=False,
        verbose=False
    )

    best_plate = ""
    best_combined_score = 0.0

    for result in results:
        if result.boxes is None:
            continue

        for i in range(
            len(result.boxes.cls)
        ):
            detected_class = int(
                result.boxes.cls[i].item()
            )

            detection_confidence = float(
                result.boxes.conf[i].item()
            )

            # Clase 0 = matrícula
            if detected_class != 0:
                continue

            if (
                detection_confidence <
                YOLO_CONFIDENCE
            ):
                continue

            coordinates = (
                result.boxes.xyxy[i].tolist()
            )

            x1, y1, x2, y2 = map(
                int,
                coordinates
            )

            image_height, image_width = (
                image.shape[:2]
            )

            # Margen alrededor de la matrícula
            x1 = max(0, x1 - 15)
            y1 = max(0, y1 - 12)
            x2 = min(
                image_width,
                x2 + 15
            )
            y2 = min(
                image_height,
                y2 + 12
            )

            plate_image = image[
                y1:y2,
                x1:x2
            ]

            if plate_image.size == 0:
                continue

            ocr_result = ocr.ocr(
                plate_image,
                cls=True
            )

            if (
                not ocr_result
                or not ocr_result[0]
            ):
                continue

            detected_texts = []
            detected_scores = []

            for line in ocr_result[0]:
                if (
                    not line
                    or len(line) < 2
                ):
                    continue

                text_data = line[1]

                if (
                    not text_data
                    or len(text_data) < 2
                ):
                    continue

                text = str(
                    text_data[0]
                )

                try:
                    score = float(
                        text_data[1]
                    )
                except (
                    TypeError,
                    ValueError
                ):
                    score = 0.0

                detected_texts.append(
                    text
                )

                detected_scores.append(
                    score
                )

            if not detected_texts:
                continue

            raw_text = "".join(
                detected_texts
            )

            cleaned_plate = clean_plate_text(
                raw_text
            )

            if not (
                MIN_PLATE_LENGTH
                <= len(cleaned_plate)
                <= MAX_PLATE_LENGTH
            ):
                continue

            if detected_scores:
                average_ocr_score = (
                    sum(detected_scores)
                    / len(detected_scores)
                )
            else:
                average_ocr_score = 0.0

            combined_score = (
                detection_confidence
                * average_ocr_score
            )

            if (
                combined_score >
                best_combined_score
            ):
                best_combined_score = (
                    combined_score
                )

                best_plate = (
                    cleaned_plate
                )

    return best_plate


# =====================================================
# ENDPOINT DE ESTADO
# =====================================================

@app.route(
    "/status",
    methods=["GET"]
)
def status():
    return jsonify({
        "ok": True,
        "status": "ANPR_READY"
    })


# =====================================================
# ENDPOINT PARA LEER MATRÍCULA
# =====================================================

@app.route(
    "/read-plate",
    methods=["POST"]
)
def read_plate():
    data = request.get_json(
        silent=True
    )

    if not isinstance(data, dict):
        return jsonify({
            "ok": False,
            "error": "INVALID_JSON"
        }), 400

    image_path = str(
        data.get(
            "image_path",
            ""
        )
    ).strip()

    if not image_path:
        return jsonify({
            "ok": False,
            "error": "IMAGE_PATH_REQUIRED"
        }), 400

    if not os.path.isfile(
        image_path
    ):
        return jsonify({
            "ok": False,
            "error": "IMAGE_NOT_FOUND",
            "image_path": image_path
        }), 404

    start_time = time.perf_counter()

    try:
        plate = detect_plate(
            image_path
        )

        elapsed_time = (
            time.perf_counter()
            - start_time
        )

        return jsonify({
            "ok": True,
            "plate": (
                plate
                if plate
                else "NO_PLATE"
            ),
            "processing_seconds": round(
                elapsed_time,
                3
            )
        })

    except Exception as error:
        elapsed_time = (
            time.perf_counter()
            - start_time
        )

        print(
            "Error procesando imagen:"
        )

        traceback.print_exc()

        return jsonify({
            "ok": False,
            "error": "OCR_ERROR",
            "detail": str(error),
            "processing_seconds": round(
                elapsed_time,
                3
            )
        }), 500

# iniciar servidor

if __name__ == "__main__":
    app.run(
        host="127.0.0.1",
        port=5000,
        debug=False,
        threaded=False
    )
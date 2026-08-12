<?php

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo "METHOD_NOT_ALLOWED";
    exit;
}

// =====================================================
// CONFIGURACIÓN
// =====================================================

$camIP = "172.20.10.4";
$serverIP = "172.20.10.14";

$uploadServer =
    "http://" . $serverIP . "/upload_image.php";

$anprServer =
    "http://127.0.0.1:5000/read-plate";

// =====================================================
// GENERAR NOMBRE ÚNICO
// =====================================================

$now = microtime(true);

$imageName = sprintf(
    "capture_%s_%06d_%s.jpg",
    date("Ymd_His", (int) $now),
    (int) (($now - floor($now)) * 1000000),
    bin2hex(random_bytes(3))
);

$imagePath =
    "C:\\xampp\\htdocs\\uploads\\" .
    $imageName;

$startTotal = microtime(true);

// =====================================================
// 1. SOLICITAR CAPTURA A LA ESP32-CAM
// =====================================================

$captureURL =
    "http://" . $camIP . "/capture" .
    "?name=" . rawurlencode($imageName) .
    "&server=" . rawurlencode($uploadServer);

$captureContext = stream_context_create([
    "http" => [
        "method" => "GET",
        "timeout" => 15,
        "ignore_errors" => true
    ]
]);

$startCapture = microtime(true);

$captureResponse = @file_get_contents(
    $captureURL,
    false,
    $captureContext
);

$captureTime =
    microtime(true) - $startCapture;

if (
    $captureResponse === false ||
    trim($captureResponse) !== "OK"
) {
    registrarTiempo(
        $captureTime,
        0,
        microtime(true) - $startTotal,
        "ERROR_CAM"
    );

    echo "ERROR_CAM";
    exit;
}

// =====================================================
// 2. ESPERAR QUE LA IMAGEN EXISTA
// =====================================================

$uploadTimeoutSeconds = 5;
$waitStart = microtime(true);

while (!file_exists($imagePath)) {
    if (
        microtime(true) - $waitStart >
        $uploadTimeoutSeconds
    ) {
        registrarTiempo(
            $captureTime,
            0,
            microtime(true) - $startTotal,
            "ERROR_UPLOAD"
        );

        echo "ERROR_UPLOAD";
        exit;
    }

    usleep(100000);
}

// =====================================================
// 3. ESPERAR QUE TERMINE LA ESCRITURA
// =====================================================

$previousSize = -1;

for ($attempt = 0; $attempt < 15; $attempt++) {
    clearstatcache(
        true,
        $imagePath
    );

    $currentSize =
        file_exists($imagePath)
            ? filesize($imagePath)
            : 0;

    if (
        $currentSize > 0 &&
        $currentSize === $previousSize
    ) {
        break;
    }

    $previousSize = $currentSize;

    usleep(100000);
}

// =====================================================
// 4. VALIDAR IMAGEN
// =====================================================

clearstatcache(
    true,
    $imagePath
);

if (
    !file_exists($imagePath) ||
    filesize($imagePath) < 5000
) {
    registrarTiempo(
        $captureTime,
        0,
        microtime(true) - $startTotal,
        "ERROR_IMAGE"
    );

    echo "ERROR_IMAGE";
    exit;
}

// =====================================================
// 5. CONSULTAR SERVIDOR ANPR
// =====================================================

$requestData = json_encode(
    [
        "image_path" => $imagePath
    ],
    JSON_UNESCAPED_SLASHES
);

$curl = curl_init($anprServer);

curl_setopt_array(
    $curl,
    [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Content-Length: " .
            strlen($requestData)
        ],
        CURLOPT_POSTFIELDS => $requestData
    ]
);

$startOCR = microtime(true);

$anprResponse = curl_exec($curl);

$ocrTime =
    microtime(true) - $startOCR;

$httpCode = curl_getinfo(
    $curl,
    CURLINFO_HTTP_CODE
);

$curlError = curl_error($curl);

curl_close($curl);

// =====================================================
// 6. VALIDAR RESPUESTA DEL SERVIDOR ANPR
// =====================================================

if (
    $anprResponse === false ||
    $httpCode !== 200
) {
    file_put_contents(
        __DIR__ . "/log.txt",
        date("Y-m-d H:i:s") .
        " - ANPR SERVER ERROR" .
        " | HTTP: " . $httpCode .
        " | CURL: " . $curlError .
        " | RESPONSE: " .
        (string) $anprResponse .
        PHP_EOL,
        FILE_APPEND
    );

    registrarTiempo(
        $captureTime,
        $ocrTime,
        microtime(true) - $startTotal,
        "ERROR_OCR"
    );

    echo "ERROR_OCR";
    exit;
}

$anprResult = json_decode(
    $anprResponse,
    true
);

if (
    !is_array($anprResult) ||
    ($anprResult["ok"] ?? false) !== true
) {
    file_put_contents(
        __DIR__ . "/log.txt",
        date("Y-m-d H:i:s") .
        " - INVALID ANPR RESPONSE: " .
        $anprResponse .
        PHP_EOL,
        FILE_APPEND
    );

    registrarTiempo(
        $captureTime,
        $ocrTime,
        microtime(true) - $startTotal,
        "ERROR_OCR"
    );

    echo "ERROR_OCR";
    exit;
}

// =====================================================
// 7. LIMPIAR MATRÍCULA
// =====================================================

$plate = strtoupper(
    trim(
        (string) (
            $anprResult["plate"] ?? ""
        )
    )
);

$plate = preg_replace(
    "/[^A-Z0-9]/",
    "",
    $plate
);

if (
    $plate === "NOPLATE" ||
    strlen($plate) < 5 ||
    strlen($plate) > 8
) {
    $plate = "";
}

// =====================================================
// 8. REGISTRAR RESULTADO Y TIEMPOS
// =====================================================

$totalTime =
    microtime(true) - $startTotal;

$resultText =
    $plate !== ""
        ? $plate
        : "NO_PLATE";

registrarTiempo(
    $captureTime,
    $ocrTime,
    $totalTime,
    $resultText
);

file_put_contents(
    __DIR__ . "/log.txt",
    date("Y-m-d H:i:s") .
    " - " . $resultText .
    " | Imagen: " . $imageName .
    " | OCR API: " .
    ($anprResult["processing_seconds"] ?? "N/A") .
    " s" .
    PHP_EOL,
    FILE_APPEND
);

// =====================================================
// 9. RESPUESTA
// =====================================================

echo $resultText;


// =====================================================
// FUNCIÓN PARA REGISTRAR TIEMPOS
// =====================================================

function registrarTiempo(
    float $captureTime,
    float $ocrTime,
    float $totalTime,
    string $result
): void {
    file_put_contents(
        __DIR__ . "/tiempos.txt",
        sprintf(
            "%s | Captura/subida: %.3f s | OCR: %.3f s | Total: %.3f s | Resultado: %s%s",
            date("Y-m-d H:i:s"),
            $captureTime,
            $ocrTime,
            $totalTime,
            $result,
            PHP_EOL
        ),
        FILE_APPEND
    );
}
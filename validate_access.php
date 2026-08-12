<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "parking_access"
);

if ($conn->connect_error) {
    die("DB_ERROR");
}

$conn->set_charset("utf8mb4");

$uid = strtoupper(
    trim($_GET["uid"] ?? "")
);

if ($uid === "") {
    echo "INVALID_UID";
    exit;
}

// =========================
// 1) VALIDAR RFID
// =========================

$stmt = $conn->prepare("
    SELECT id
    FROM rfid_cards
    WHERE UPPER(TRIM(card_uid)) = ?
      AND access_status = 'ACTIVE'
");

$stmt->bind_param("s", $uid);
$stmt->execute();

$result = $stmt->get_result();

$rfid_ok = (
    $result &&
    $result->num_rows > 0
);

$stmt->close();

// =========================
// 2) CAPTURAR + OCR
// UNA SOLA FOTO POR INTENTO
// =========================

$response = @file_get_contents(
    "http://localhost/capture.php?t=" .
    microtime(true)
);

if ($response === false) {
    $plate = "ERROR_CAM";
} else {
    $plate = strtoupper(
        trim($response)
    );
}

// =========================
// 3) MANEJO DE ERRORES OCR
// =========================

$erroresSistema = [
    "ERROR_CAM",
    "ERROR_UPLOAD",
    "ERROR_IMAGE",
    "ERROR_OCR"
];

if (in_array(
    $plate,
    $erroresSistema,
    true
)) {
    $stmt_err = $conn->prepare("
        INSERT INTO access_log (
            card_uid,
            plate_number,
            access_status,
            access_time
        )
        VALUES (?, ?, 'SYSTEM_ERROR', NOW())
    ");

    $stmt_err->bind_param(
        "ss",
        $uid,
        $plate
    );

    $stmt_err->execute();
    $stmt_err->close();

    echo "SYSTEM_ERROR";

    $conn->close();
    exit;
}

// =========================
// 4) VALIDAR MATRÍCULA
// =========================

$plate_ok = false;

if (
    $plate !== "" &&
    $plate !== "NO_PLATE"
) {
    $stmt2 = $conn->prepare("
        SELECT id
        FROM vehicles
        WHERE UPPER(TRIM(plate_number)) = ?
          AND access_status = 'ACTIVE'
    ");

    $stmt2->bind_param(
        "s",
        $plate
    );

    $stmt2->execute();

    $result2 = $stmt2->get_result();

    $plate_ok = (
        $result2 &&
        $result2->num_rows > 0
    );

    $stmt2->close();
}

// =========================
// 5) DECISIÓN FINAL
// =========================

$status = "ACCESS_DENIED";

if ($rfid_ok && $plate_ok) {
    $status = "ACCESS_GRANTED";
}

// =========================
// 6) GUARDAR LOG
// =========================

$stmt3 = $conn->prepare("
    INSERT INTO access_log (
        card_uid,
        plate_number,
        access_status,
        access_time
    )
    VALUES (?, ?, ?, NOW())
");

$stmt3->bind_param(
    "sss",
    $uid,
    $plate,
    $status
);

$stmt3->execute();
$stmt3->close();

// =========================
// 7) ACTUALIZAR ÚLTIMO ACCESO
// SOLO SI FUE PERMITIDO
// =========================

if ($status === "ACCESS_GRANTED") {
    $stmt4 = $conn->prepare("
        UPDATE rfid_cards
        SET last_access = NOW()
        WHERE UPPER(TRIM(card_uid)) = ?
    ");

    $stmt4->bind_param(
        "s",
        $uid
    );

    $stmt4->execute();
    $stmt4->close();
}

// =========================
// RESPUESTA FINAL
// =========================

echo $status;

$conn->close();

?>
<?php
$conn = new mysqli("localhost", "root", "", "parking_access");

if ($conn->connect_error) {
    die("DB_ERROR");
}

$uid = strtoupper(trim($_GET['uid'] ?? ''));

if ($uid == "") {
    echo "INVALID_UID";
    exit;
}

// =========================
// 1) VALIDAR RFID
// =========================
$stmt = $conn->prepare("
    SELECT id FROM rfid_cards
    WHERE UPPER(TRIM(card_uid)) = ?
    AND access_status = 'ACTIVE'
");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

$rfid_ok = ($result && $result->num_rows > 0);

// =========================
// 2) CAPTURAR + OCR
// =========================
$plate = @file_get_contents("http://localhost/capture.php");
$plate = strtoupper(trim($plate));

// errores del subsistema OCR
if (in_array($plate, ["ERROR_CAM", "ERROR_UPLOAD", "ERROR_IMAGE", "ERROR_OCR"])) {

    $conn->query("
        INSERT INTO access_log (card_uid, plate_number, access_status)
        VALUES ('$uid', '$plate', 'SYSTEM_ERROR')
    ");

    echo "SYSTEM_ERROR";
    exit;
}

// =========================
// 3) VALIDAR MATRÍCULA
// =========================
$plate_ok = false;

if ($plate != "" && $plate != "NO_PLATE") {
    $stmt2 = $conn->prepare("
        SELECT id FROM matriculas
        WHERE UPPER(TRIM(numero)) = ?
        AND access_status = 'ACTIVE'
    ");
    $stmt2->bind_param("s", $plate);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    $plate_ok = ($result2 && $result2->num_rows > 0);
}

// =========================
// 4) DECISIÓN FINAL
// =========================
if ($rfid_ok && $plate_ok) {
    $status = "ACCESS_GRANTED";
} else {
    $status = "ACCESS_DENIED";
}

echo $status;

// =========================
// 5) LOG COMPLETO
// =========================
if (!isset($status) || $status == ""){
    $status = "UNKNOWN";
}
$stmt3 = $conn->prepare("
    INSERT INTO access_log (card_uid, plate_number, access_status)
    VALUES (?, ?, ?)
");
$stmt3->bind_param("sss", $uid, $plate, $status);
$stmt3->execute();

$conn->close();
?>
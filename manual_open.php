<?php

header("Content-Type: text/plain; charset=UTF-8");

const ESP32_TOKEN =
    "ANDE_BARRERA_2026_SEGURA";

$token = trim(
    $_GET["token"] ?? ""
);

if (!hash_equals(ESP32_TOKEN, $token)) {
    http_response_code(403);
    echo "UNAUTHORIZED";
    exit;
}

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "parking_access"
);

if ($conn->connect_error) {
    http_response_code(500);
    echo "DB_ERROR";
    exit;
}

$conn->set_charset("utf8mb4");

/*
 * Bloqueamos temporalmente la fila para impedir
 * que una misma orden se ejecute varias veces.
 */
$conn->begin_transaction();

$resultado = $conn->query("
    SELECT id
    FROM barrier_commands
    WHERE status = 'PENDING'
      AND command_type = 'OPEN'
    ORDER BY id ASC
    LIMIT 1
    FOR UPDATE
");

if (
    !$resultado ||
    $resultado->num_rows === 0
) {
    $conn->commit();
    $conn->close();

    echo "NO_COMMAND";
    exit;
}

$fila = $resultado->fetch_assoc();
$commandId = (int) $fila["id"];

$stmt = $conn->prepare("
    UPDATE barrier_commands
    SET
        status = 'EXECUTED',
        executed_at = NOW()
    WHERE id = ?
      AND status = 'PENDING'
");

$stmt->bind_param(
    "i",
    $commandId
);

$stmt->execute();

if ($stmt->affected_rows === 1) {
    $conn->commit();

    echo "OPEN:" . $commandId;
} else {
    $conn->rollback();

    echo "NO_COMMAND";
}

$stmt->close();
$conn->close();
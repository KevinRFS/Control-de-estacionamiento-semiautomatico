<?php

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "parking_access"
);

if ($conn->connect_error) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión: " . $conn->connect_error
    ]);

    exit;
}

$conn->set_charset("utf8mb4");

$sql = "
    SELECT
        id,
        card_uid,
        owner_name,
        access_status,
        last_access
    FROM rfid_cards
    ORDER BY owner_name ASC
";

$resultado = $conn->query($sql);

if (!$resultado) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error en la consulta: " . $conn->error
    ]);

    $conn->close();
    exit;
}

$usuarios = [];

while ($fila = $resultado->fetch_assoc()) {
    $usuarios[] = [
        "id" => (int) $fila["id"],
        "card_uid" => $fila["card_uid"],
        "owner_name" => $fila["owner_name"],
        "access_status" => $fila["access_status"],
        "last_access" => $fila["last_access"]
    ];
}

echo json_encode(
    $usuarios,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

$conn->close();
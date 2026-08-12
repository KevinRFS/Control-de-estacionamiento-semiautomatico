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
        plate_number,
        vehicle_group,
        access_status
    FROM vehicles
    ORDER BY id DESC
";

$res = $conn->query($sql);

if (!$res) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error en la consulta: " . $conn->error
    ]);

    $conn->close();
    exit;
}

$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

$conn->close();
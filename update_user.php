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
        "mensaje" => "Error de conexión a la base de datos."
    ]);

    exit;
}

$conn->set_charset("utf8mb4");

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$uid = strtoupper(
    trim($data["uid"] ?? "")
);

$nombre = trim(
    $data["nombre"] ?? ""
);

$estado = strtoupper(
    trim($data["estado"] ?? "")
);

$estadosPermitidos = [
    "ACTIVE",
    "INACTIVE"
];

if ($uid === "") {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "El UID es obligatorio."
    ]);

    exit;
}

if ($nombre === "") {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "El nombre es obligatorio."
    ]);

    exit;
}

if (!in_array($estado, $estadosPermitidos, true)) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "El estado seleccionado no es válido."
    ]);

    exit;
}

$stmt = $conn->prepare("
    UPDATE rfid_cards
    SET
        owner_name = ?,
        access_status = ?
    WHERE card_uid = ?
");

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando la consulta: " . $conn->error
    ]);

    $conn->close();
    exit;
}

$stmt->bind_param(
    "sss",
    $nombre,
    $estado,
    $uid
);

if (!$stmt->execute()) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo actualizar el usuario."
    ]);

    $stmt->close();
    $conn->close();
    exit;
}

if ($stmt->affected_rows > 0) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Usuario actualizado correctamente."
    ]);
} else {
    echo json_encode([
        "ok" => true,
        "mensaje" => "No hubo cambios en el usuario."
    ]);
}

$stmt->close();
$conn->close();
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
        "mensaje" => "Error de conexión."
    ]);

    exit;
}

$conn->set_charset("utf8mb4");

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$patente = strtoupper(
    trim($data["patente"] ?? "")
);

$grupo = trim(
    $data["grupo"] ?? ""
);

$estado = trim(
    $data["estado"] ?? ""
);

$gruposPermitidos = [
    "MOVIL_ANDE",
    "FUNCIONARIO_ANDE",
    "CONTRATISTA",
    "PARTICULAR"
];

$estadosPermitidos = [
    "ACTIVE",
    "INACTIVE"
];

if ($patente === "") {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "La matrícula es obligatoria."
    ]);

    exit;
}

if (!in_array($grupo, $gruposPermitidos, true)) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "El grupo seleccionado no es válido."
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
    UPDATE vehicles
    SET
        vehicle_group = ?,
        access_status = ?
    WHERE plate_number = ?
");

$stmt->bind_param(
    "sss",
    $grupo,
    $estado,
    $patente
);

$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Vehículo actualizado correctamente."
    ]);
} else {
    echo json_encode([
        "ok" => true,
        "mensaje" => "No hubo cambios en el vehículo."
    ]);
}

$stmt->close();
$conn->close();
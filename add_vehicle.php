<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "parking_access");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión con la base de datos"
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents("php://input"), true);

$patente = strtoupper(trim($data['patente'] ?? ''));
$grupo   = trim($data['grupo'] ?? '');

$gruposPermitidos = [
    'MOVIL_ANDE',
    'FUNCIONARIO_ANDE',
    'CONTRATISTA',
    'PARTICULAR'
];

if ($patente === '') {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "mensaje" => "La matrícula es obligatoria"
    ]);
    exit;
}

if (!in_array($grupo, $gruposPermitidos, true)) {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "mensaje" => "Debe seleccionar un grupo válido"
    ]);
    exit;
}

// Evita registrar dos veces la misma matrícula.
$consulta = $conn->prepare("SELECT id FROM vehicles WHERE plate_number = ? LIMIT 1");
$consulta->bind_param("s", $patente);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "ok" => false,
        "mensaje" => "La matrícula ya está registrada"
    ]);
    exit;
}

$stmt = $conn->prepare("\n    INSERT INTO vehicles (plate_number, vehicle_group, access_status)\n    VALUES (?, ?, 'ACTIVE')\n");
$stmt->bind_param("ss", $patente, $grupo);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo registrar el vehículo"
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "mensaje" => "Vehículo registrado correctamente",
    "vehiculo" => [
        "patente" => $patente,
        "grupo" => $grupo
    ]
]);
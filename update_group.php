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

$id = isset($data["id"])
    ? (int) $data["id"]
    : 0;

$grupo = strtoupper(
    trim($data["grupo"] ?? "")
);

$revisadoPor = trim(
    $data["revisado_por"] ?? ""
);

$gruposPermitidos = [
    "MOVIL_ANDE",
    "FUNCIONARIO_ANDE",
    "CONTRATISTA",
    "PARTICULAR"
];

if ($id <= 0) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "El registro seleccionado no es válido."
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

if ($revisadoPor === "") {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Debe indicar quién realizó la clasificación."
    ]);

    exit;
}

$stmt = $conn->prepare("
    UPDATE access_log
    SET
        manual_group = ?,
        reviewed_by = ?,
        reviewed_at = NOW()
    WHERE id = ?
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
    "ssi",
    $grupo,
    $revisadoPor,
    $id
);

if (!$stmt->execute()) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo actualizar el registro."
    ]);

    $stmt->close();
    $conn->close();
    exit;
}

if ($stmt->affected_rows > 0) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Registro clasificado correctamente."
    ]);
} else {
    echo json_encode([
        "ok" => true,
        "mensaje" => "No se realizaron cambios."
    ]);
}

$stmt->close();
$conn->close();
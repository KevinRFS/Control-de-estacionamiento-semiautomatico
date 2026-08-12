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

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Los datos enviados no son válidos."
    ]);

    $conn->close();
    exit;
}

$visitorName = trim(
    $data["visitor_name"] ?? ""
);

$visitedPerson = trim(
    $data["visited_person"] ?? ""
);

$reason = trim(
    $data["reason"] ?? ""
);

$plateNumber = strtoupper(
    trim($data["plate_number"] ?? "")
);

$vehicleGroup = strtoupper(
    trim($data["vehicle_group"] ?? "")
);

$requestedBy = trim(
    $data["requested_by"] ?? ""
);

$gruposPermitidos = [
    "MOVIL_ANDE",
    "FUNCIONARIO_ANDE",
    "CONTRATISTA",
    "PARTICULAR"
];

if ($visitorName === "") {
    responderError(
        $conn,
        "Ingresá el nombre del visitante."
    );
}

if ($reason === "") {
    responderError(
        $conn,
        "Ingresá el motivo del ingreso."
    );
}

if ($requestedBy === "") {
    responderError(
        $conn,
        "Ingresá el nombre del responsable."
    );
}

if (
    !in_array(
        $vehicleGroup,
        $gruposPermitidos,
        true
    )
) {
    responderError(
        $conn,
        "El grupo del vehículo no es válido."
    );
}

if ($plateNumber === "") {
    $plateNumber = "NO_PLATE";
}

/*
 * Evitar varias aperturas manuales pendientes.
 */
$verificar = $conn->query("
    SELECT id
    FROM barrier_commands
    WHERE status = 'PENDING'
    LIMIT 1
");

if (
    $verificar &&
    $verificar->num_rows > 0
) {
    responderError(
        $conn,
        "Ya existe una apertura manual pendiente."
    );
}

/*
 * Nombre único para la fotografía.
 */
$photoFilename =
    "manual_" .
    date("Ymd_His") .
    "_" .
    preg_replace(
        "/[^A-Z0-9]/",
        "",
        $plateNumber
    ) .
    ".jpg";

/*
 * Registrar la orden manual.
 */
$stmtCommand = $conn->prepare("
    INSERT INTO barrier_commands (
        command_type,
        requested_by,
        visitor_name,
        visited_person,
        reason,
        plate_number,
        vehicle_group,
        photo_filename,
        photo_status,
        status
    )
    VALUES (
        'OPEN',
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        'PENDING',
        'PENDING'
    )
");

if (!$stmtCommand) {
    responderError(
        $conn,
        "No se pudo preparar la orden: " .
        $conn->error,
        500
    );
}

$stmtCommand->bind_param(
    "sssssss",
    $requestedBy,
    $visitorName,
    $visitedPerson,
    $reason,
    $plateNumber,
    $vehicleGroup,
    $photoFilename
);

if (!$stmtCommand->execute()) {
    responderError(
        $conn,
        "No se pudo registrar la orden.",
        500
    );
}

$commandId = $stmtCommand->insert_id;

$stmtCommand->close();

/*
 * Registrar el acceso en el historial.
 *
 * Se usa SIN_RFID porque el acceso fue autorizado
 * manualmente por el portero.
 */
$manualUid = "SIN_RFID";

$stmtLog = $conn->prepare("
    INSERT INTO access_log (
        card_uid,
        plate_number,
        access_status,
        access_type,
        visitor_name,
        visited_person,
        access_reason,
        authorized_by,
        photo_filename,
        access_time,
        manual_group,
        reviewed_by,
        reviewed_at
    )
    VALUES (
        ?,
        ?,
        'ACCESS_GRANTED',
        'MANUAL',
        ?,
        ?,
        ?,
        ?,
        ?,
        NOW(),
        ?,
        ?,
        NOW()
    )
");

if (!$stmtLog) {
    $conn->query("
        UPDATE barrier_commands
        SET status = 'CANCELLED'
        WHERE id = " . (int) $commandId
    );

    responderError(
        $conn,
        "No se pudo preparar el historial: " .
        $conn->error,
        500
    );
}

$stmtLog->bind_param(
    "sssssssss",
    $manualUid,
    $plateNumber,
    $visitorName,
    $visitedPerson,
    $reason,
    $requestedBy,
    $photoFilename,
    $vehicleGroup,
    $requestedBy
);

if (!$stmtLog->execute()) {
    $conn->query("
        UPDATE barrier_commands
        SET status = 'CANCELLED'
        WHERE id = " . (int) $commandId
    );

    responderError(
        $conn,
        "No se pudo registrar el acceso manual.",
        500
    );
}

$stmtLog->close();

echo json_encode([
    "ok" => true,
    "mensaje" =>
        "Acceso manual autorizado. " .
        "La barrera abrirá cuando el ESP32 reciba la orden.",
    "command_id" => $commandId,
    "photo_filename" => $photoFilename
], JSON_UNESCAPED_UNICODE);

$conn->close();

function responderError(
    mysqli $conn,
    string $mensaje,
    int $codigo = 400
): void {
    http_response_code($codigo);

    echo json_encode([
        "ok" => false,
        "mensaje" => $mensaje
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}
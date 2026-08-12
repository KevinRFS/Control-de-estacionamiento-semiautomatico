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

$sql = "
    SELECT
        al.id,
        al.card_uid,

        CASE
            WHEN al.access_type = 'MANUAL'
                 AND al.visitor_name IS NOT NULL
                 AND TRIM(al.visitor_name) <> ''
                THEN al.visitor_name

            WHEN rc.owner_name IS NOT NULL
                 AND TRIM(rc.owner_name) <> ''
                THEN rc.owner_name

            ELSE 'No registrado'
        END AS owner_name,

        al.plate_number,

        CASE
            WHEN al.manual_group IS NOT NULL
                 AND TRIM(al.manual_group) <> ''
                THEN al.manual_group

            WHEN v.vehicle_group IS NOT NULL
                 AND TRIM(v.vehicle_group) <> ''
                THEN v.vehicle_group

            ELSE 'PENDIENTE_REVISION'
        END AS vehicle_group,

        CASE
            WHEN al.access_type = 'MANUAL'
                THEN 'MANUAL'

            WHEN al.manual_group IS NOT NULL
                 AND TRIM(al.manual_group) <> ''
                THEN 'MANUAL'

            WHEN v.vehicle_group IS NOT NULL
                 AND TRIM(v.vehicle_group) <> ''
                THEN 'AUTOMATICO'

            ELSE 'PENDIENTE'
        END AS group_source,

        al.manual_group,
        al.reviewed_by,
        al.reviewed_at,

        al.access_status,
        al.access_type,
        al.visitor_name,
        al.visited_person,
        al.access_reason,
        al.authorized_by,
        al.photo_filename,
        al.access_time

    FROM access_log al

    LEFT JOIN rfid_cards rc
        ON UPPER(TRIM(rc.card_uid)) =
           UPPER(TRIM(al.card_uid))

    LEFT JOIN vehicles v
        ON UPPER(TRIM(v.plate_number)) =
           UPPER(TRIM(al.plate_number))

    ORDER BY al.access_time DESC
";

$resultado = $conn->query($sql);

if (!$resultado) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error ejecutando la consulta.",
        "detalle" => $conn->error
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}

$registros = [];

while ($fila = $resultado->fetch_assoc()) {
    $registros[] = [
        "id" => (int) $fila["id"],

        "card_uid" => $fila["card_uid"],

        "owner_name" => $fila["owner_name"],

        "plate_number" => $fila["plate_number"],

        "vehicle_group" => $fila["vehicle_group"],

        "group_source" => $fila["group_source"],

        "manual_group" => $fila["manual_group"],

        "reviewed_by" => $fila["reviewed_by"],

        "reviewed_at" => $fila["reviewed_at"],

        "access_status" => $fila["access_status"],

        "access_type" => $fila["access_type"],

        "visitor_name" => $fila["visitor_name"],

        "visited_person" => $fila["visited_person"],

        "access_reason" => $fila["access_reason"],

        "authorized_by" => $fila["authorized_by"],

        "photo_filename" => $fila["photo_filename"],

        "access_time" => $fila["access_time"]
    ];
}

echo json_encode(
    $registros,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

$conn->close();
<?php

header(
    "Content-Type: application/json; charset=utf-8"
);

$mysqli = new mysqli(
    "localhost",
    "root",
    "",
    "parking_access"
);

if ($mysqli->connect_error) {
    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "error" => "DB_ERROR"
    ]);

    exit;
}

$result =
    $mysqli->query("
        SELECT
            mode,
            updated_by,
            updated_at
        FROM barrier_system_state
        WHERE id = 1
        LIMIT 1
    ");

if (
    !$result ||
    $result->num_rows === 0
) {
    echo json_encode([
        "ok" => true,
        "mode" => "AUTO"
    ]);

    exit;
}

$row =
    $result->fetch_assoc();

echo json_encode([
    "ok" => true,
    "mode" =>
        $row["mode"],
    "updated_by" =>
        $row["updated_by"],
    "updated_at" =>
        $row["updated_at"]
]);

$mysqli->close();
?>
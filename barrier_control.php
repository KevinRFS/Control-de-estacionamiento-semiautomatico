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

$mysqli->set_charset(
    "utf8mb4"
);

// =====================================================
// RECIBIR ACCIÓN
// =====================================================

$data = json_decode(
    file_get_contents(
        "php://input"
    ),
    true
);

$action =
    strtoupper(
        trim(
            $data["action"] ?? ""
        )
    );

$user =
    trim(
        $data["requested_by"] ??
        "DASHBOARD"
    );

// =====================================================
// VALIDAR
// =====================================================

if (
    !in_array(
        $action,
        [
            "HOLD_OPEN",
            "RESUME_AUTO"
        ],
        true
    )
) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "error" =>
            "INVALID_ACTION"
    ]);

    exit;
}

// =====================================================
// DETERMINAR MODO
// =====================================================

$newMode =
    $action === "HOLD_OPEN"
        ? "HOLD_OPEN"
        : "AUTO";

try {

    $mysqli->begin_transaction();

    // =================================================
    // CREAR COMANDO
    // =================================================

    $stmt =
        $mysqli->prepare("
            INSERT INTO barrier_commands (
                command_type,
                requested_by,
                status,
                created_at
            )
            VALUES (
                ?,
                ?,
                'PENDING',
                NOW()
            )
        ");

    $stmt->bind_param(
        "ss",
        $action,
        $user
    );

    $stmt->execute();

    $commandId =
        $mysqli->insert_id;

    // =================================================
    // ACTUALIZAR ESTADO DEL SISTEMA
    // =================================================

    $stmtState =
        $mysqli->prepare("
            INSERT INTO barrier_system_state (
                id,
                mode,
                updated_by,
                updated_at
            )
            VALUES (
                1,
                ?,
                ?,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                mode = VALUES(mode),
                updated_by = VALUES(updated_by),
                updated_at = NOW()
        ");

    $stmtState->bind_param(
        "ss",
        $newMode,
        $user
    );

    $stmtState->execute();

    $mysqli->commit();

    echo json_encode([
        "ok" => true,
        "command_id" =>
            $commandId,
        "command" =>
            $action,
        "mode" =>
            $newMode
    ]);

}
catch (Throwable $error) {

    $mysqli->rollback();

    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "error" =>
            "SERVER_ERROR"
    ]);
}

$mysqli->close();
?>
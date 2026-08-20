<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Dashboard de accesos</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f1f5f9;
            color: #0f172a;
        }

        /* ============================================
           SIDEBAR
        ============================================ */

        .sidebar {
            width: 250px;
            min-height: 100vh;
            padding: 20px;
            background: #0f172a;
            color: white;
        }

        .sidebar h2 {
            margin-bottom: 30px;
        }

        .sidebar a,
        .sidebar button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: transparent;
            color: #cbd5e1;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .sidebar a:hover,
        .sidebar a.activo,
        .sidebar button:hover {
            background: #1e293b;
            color: white;
        }

        /* ACCESO MANUAL */

        .sidebar .btn-acceso-manual {
            margin-top: 30px;
            background: #16a34a;
            color: white;
            font-weight: 600;
            text-align: center;
        }

        .sidebar .btn-acceso-manual:hover {
            background: #15803d;
        }

        /* ============================================
           CONTROL BARRERA
        ============================================ */

        .control-barrera {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #334155;
        }

        .control-barrera-titulo {
            margin-bottom: 10px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar .btn-barrera {
            margin-bottom: 8px;
            padding: 11px;
            background: #2563eb;
            color: white;
            font-weight: 600;
            text-align: center;
            transition: 0.2s;
        }

        .sidebar .btn-barrera:hover {
            background: #1d4ed8;
        }

        .sidebar .btn-barrera.modo-abierto {
            background: #dc2626;
        }

        .sidebar .btn-barrera.modo-abierto:hover {
            background: #b91c1c;
        }

        .sidebar .btn-barrera:disabled {
            background: #475569;
            cursor: wait;
            opacity: 0.8;
        }

        .estado-barrera {
            padding: 9px 10px;
            border-radius: 6px;
            background: #1e293b;
            color: #cbd5e1;
            text-align: center;
            font-size: 12px;
            line-height: 1.4;
        }

        .estado-barrera.auto {
            border-left: 4px solid #22c55e;
        }

        .estado-barrera.abierta {
            border-left: 4px solid #ef4444;
            background: #3f1d1d;
            color: #fecaca;
            font-weight: 600;
        }

        .estado-barrera.error {
            border-left: 4px solid #f59e0b;
        }

        /* ============================================
           CONTENIDO PRINCIPAL
        ============================================ */

        .main {
            flex: 1;
            min-width: 0;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        /* ============================================
           ALERTA BARRERA ABIERTA
        ============================================ */

        .alerta-barrera {
            display: none;
            margin-bottom: 20px;
            padding: 15px 18px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fef2f2;
            color: #991b1b;
        }

        .alerta-barrera.visible {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .alerta-barrera strong {
            display: block;
            margin-bottom: 3px;
        }

        .alerta-barrera small {
            color: #b91c1c;
        }

        .alerta-barrera button {
            flex-shrink: 0;
            padding: 9px 14px;
            border: none;
            border-radius: 6px;
            background: #dc2626;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        .alerta-barrera button:hover {
            background: #b91c1c;
        }

        /* ============================================
           CARDS
        ============================================ */

        .cards {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow:
                0 5px 10px rgba(0, 0, 0, 0.05);
        }

        .card h3 {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        .card h1 {
            margin-top: 10px;
            font-size: 30px;
        }

        /* ============================================
           TOOLBAR
        ============================================ */

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
        }

        .toolbar input,
        .toolbar select {
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background: white;
        }

        .toolbar input {
            width: 270px;
        }

        #resultadoFiltro {
            margin-left: auto;
            color: #64748b;
            font-size: 13px;
        }

        /* ============================================
           TABLA
        ============================================ */

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
        }

        table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #0f172a;
            color: white;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .granted {
            color: green;
            font-weight: bold;
        }

        .denied {
            color: red;
            font-weight: bold;
        }

        .system-error {
            color: #d97706;
            font-weight: bold;
        }

        .pendiente {
            color: #d97706;
            font-weight: bold;
        }

        .manual {
            color: #2563eb;
            font-weight: 600;
        }

        .btn-clasificar {
            padding: 7px 11px;
            border: none;
            border-radius: 5px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-clasificar:hover {
            background: #1d4ed8;
        }

        .sin-resultados {
            padding: 25px;
            color: #64748b;
        }

        /* ============================================
           MENSAJES
        ============================================ */

        .mensaje {
            display: none;
            margin-bottom: 12px;
            padding: 12px;
            border-radius: 6px;
        }

        .mensaje.exito {
            display: block;
            background: #dcfce7;
            color: #166534;
        }

        .mensaje.error {
            display: block;
            background: #fee2e2;
            color: #991b1b;
        }

        .mensaje.info {
            display: block;
            background: #dbeafe;
            color: #1e40af;
        }

        /* ============================================
           MODALES
        ============================================ */

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.65);
        }

        .modal.abierto {
            display: flex;
        }

        .modal-contenido {
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 24px;
            border-radius: 10px;
            background: white;
            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.25);
        }

        .modal-contenido h3 {
            margin-bottom: 8px;
        }

        .descripcion-modal {
            margin-bottom: 18px;
            color: #64748b;
            font-size: 14px;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 15px;
        }

        .campo label {
            font-size: 14px;
            font-weight: 600;
        }

        .campo input,
        .campo select,
        .campo textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
        }

        .campo textarea {
            min-height: 90px;
            resize: vertical;
        }

        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            border-color: #2563eb;
        }

        .campo small {
            color: #64748b;
            font-size: 12px;
        }

        .modal-botones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-botones button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancelar {
            background: #64748b;
            color: white;
        }

        .btn-guardar {
            background: #16a34a;
            color: white;
        }

        .btn-guardar:hover {
            background: #15803d;
        }

        .btn-guardar:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        /* ============================================
           MODAL CONTROL BARRERA
        ============================================ */

        .icono-barrera {
            margin-bottom: 10px;
            font-size: 42px;
            text-align: center;
        }

        .advertencia-barrera {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 13px;
            line-height: 1.5;
        }

        .btn-peligro {
            background: #dc2626;
            color: white;
        }

        .btn-peligro:hover {
            background: #b91c1c;
        }

        /* ============================================
           RESPONSIVE
        ============================================ */

        @media (max-width: 1050px) {
            .cards {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }

        @media (max-width: 700px) {
            body {
                display: block;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .toolbar input,
            .toolbar select {
                width: 100%;
            }

            #resultadoFiltro {
                width: 100%;
                margin-left: 0;
            }

            .modal-botones {
                flex-direction: column;
            }

            .modal-botones button {
                width: 100%;
            }

            .alerta-barrera.visible {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<!-- ==================================================
     SIDEBAR
================================================== -->

<div class="sidebar">

    <h2>Accesos</h2>

    <a
        href="dashboard.php"
        class="activo"
    >
        Resumen
    </a>

    <a href="vehicles.php">
        Vehículos
    </a>

    <a href="users.php">
        Usuarios
    </a>

    <button
        type="button"
        class="btn-acceso-manual"
        onclick="abrirModalAccesoManual()"
    >
        + Acceso manual
    </button>

    <!-- ============================================
         NUEVO: CONTROL DE BARRERA
    ============================================ -->

    <div class="control-barrera">

        <div class="control-barrera-titulo">
            Control de barrera
        </div>

        <button
            type="button"
            id="btnControlBarrera"
            class="btn-barrera"
            onclick="abrirModalControlBarrera()"
        >
            🔓 Mantener barrera abierta
        </button>

        <div
            id="estadoBarreraSidebar"
            class="estado-barrera auto"
        >
            Modo automático activo
        </div>

    </div>

</div>

<!-- ==================================================
     PRINCIPAL
================================================== -->

<div class="main">

    <div class="header">

        <h2>Resumen de accesos</h2>

        <span id="hora"></span>

    </div>

    <!-- ============================================
         NUEVO: ALERTA MODO BARRERA ABIERTA
    ============================================ -->

    <div
        id="alertaBarreraAbierta"
        class="alerta-barrera"
    >

        <div>
            <strong>
                ⚠ Barrera abierta permanentemente
            </strong>

            <small>
                El control automático de cierre se encuentra
                suspendido hasta restablecer el modo automático.
            </small>
        </div>

        <button
            type="button"
            onclick="solicitarRestablecerAutomatico()"
        >
            Restablecer modo automático
        </button>

    </div>

    <!-- ============================================
         CARDS
    ============================================ -->

    <div class="cards">

        <div class="card">
            <h3>Total general</h3>
            <h1 id="totalGeneral">0</h1>
        </div>

        <div class="card">
            <h3>Total hoy</h3>
            <h1 id="totalHoy">0</h1>
        </div>

        <div class="card">
            <h3>Permitidos hoy</h3>
            <h1 id="grantedHoy">0</h1>
        </div>

        <div class="card">
            <h3>Pendientes de revisión</h3>
            <h1 id="pendientes">0</h1>
        </div>

    </div>

    <div
        id="mensaje"
        class="mensaje"
    ></div>

    <!-- ============================================
         FILTROS
    ============================================ -->

    <div class="toolbar">

        <input
            type="text"
            id="busqueda"
            placeholder="Buscar matrícula, UID o dueño..."
        >

        <select id="grupo">

            <option value="todo">
                Todos los grupos
            </option>

            <option value="MOVIL_ANDE">
                Móvil ANDE
            </option>

            <option value="FUNCIONARIO_ANDE">
                Móvil de funcionario de ANDE
            </option>

            <option value="CONTRATISTA">
                Móvil de contratista
            </option>

            <option value="PARTICULAR">
                Móvil particular
            </option>

            <option value="PENDIENTE_REVISION">
                Pendiente de revisión
            </option>

        </select>

        <select id="periodo">

            <option value="todo">
                Todo el historial
            </option>

            <option value="hoy">
                Hoy
            </option>

            <option value="semana">
                Últimos 7 días
            </option>

            <option value="mes">
                Este mes
            </option>

        </select>

        <span id="resultadoFiltro"></span>

    </div>

    <!-- ============================================
         TABLA
    ============================================ -->

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>UID</th>
                    <th>Dueño</th>
                    <th>Matrícula</th>
                    <th>Grupo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody id="tabla"></tbody>

        </table>

    </div>

</div>

<!-- ==================================================
     MODAL CLASIFICAR / CORREGIR
================================================== -->

<div
    id="modalClasificar"
    class="modal"
>

    <div class="modal-contenido">

        <h3 id="tituloModal">
            Clasificar registro
        </h3>

        <div class="campo">

            <label for="registroMatricula">
                Matrícula detectada
            </label>

            <input
                type="text"
                id="registroMatricula"
                readonly
            >

        </div>

        <div class="campo">

            <label for="grupoManual">
                Grupo correcto
            </label>

            <select id="grupoManual">

                <option value="">
                    Seleccione un grupo
                </option>

                <option value="MOVIL_ANDE">
                    Móvil ANDE
                </option>

                <option value="FUNCIONARIO_ANDE">
                    Móvil de funcionario de ANDE
                </option>

                <option value="CONTRATISTA">
                    Móvil de contratista
                </option>

                <option value="PARTICULAR">
                    Móvil particular
                </option>

            </select>

        </div>

        <div class="campo">

            <label
                for="revisadoPor"
                id="labelResponsable"
            >
                Clasificado por
            </label>

            <input
                type="text"
                id="revisadoPor"
                placeholder="Nombre del responsable"
            >

        </div>

        <div class="modal-botones">

            <button
                type="button"
                class="btn-cancelar"
                onclick="cerrarModalClasificar()"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="btn-guardar"
                onclick="guardarClasificacion()"
            >
                Guardar
            </button>

        </div>

    </div>

</div>

<!-- ==================================================
     MODAL ACCESO MANUAL
================================================== -->

<div
    id="modalAccesoManual"
    class="modal"
>

    <div class="modal-contenido">

        <h3>
            Autorizar acceso manual
        </h3>

        <p class="descripcion-modal">
            Utilizá esta opción para visitantes,
            familiares, matrículas no visibles,
            errores de lectura o vehículos no registrados.
        </p>

        <div class="campo">

            <label for="visitanteManual">
                Nombre del visitante
            </label>

            <input
                type="text"
                id="visitanteManual"
                placeholder="Nombre y apellido"
                maxlength="100"
            >

        </div>

        <div class="campo">

            <label for="personaVisitadaManual">
                Persona o dependencia visitada
            </label>

            <input
                type="text"
                id="personaVisitadaManual"
                placeholder="Ejemplo: Juan Pérez - Administración"
                maxlength="100"
            >

        </div>

        <div class="campo">

            <label for="matriculaManual">
                Matrícula
            </label>

            <input
                type="text"
                id="matriculaManual"
                placeholder="Dejar vacío si no está visible"
                maxlength="20"
            >

            <small>
                Si queda vacío, el sistema registrará
                NO_PLATE.
            </small>

        </div>

        <div class="campo">

            <label for="grupoVehiculoManual">
                Grupo del vehículo
            </label>

            <select id="grupoVehiculoManual">

                <option value="">
                    Seleccione un grupo
                </option>

                <option value="MOVIL_ANDE">
                    Móvil ANDE
                </option>

                <option value="FUNCIONARIO_ANDE">
                    Móvil de funcionario de ANDE
                </option>

                <option value="CONTRATISTA">
                    Móvil de contratista
                </option>

                <option value="PARTICULAR">
                    Móvil particular
                </option>

            </select>

        </div>

        <div class="campo">

            <label for="motivoManual">
                Motivo del ingreso
            </label>

            <textarea
                id="motivoManual"
                placeholder="Describa brevemente el motivo"
                maxlength="255"
            ></textarea>

        </div>

        <div class="campo">

            <label for="responsableManual">
                Autorizado por
            </label>

            <input
                type="text"
                id="responsableManual"
                placeholder="Nombre del portero o responsable"
                maxlength="100"
            >

        </div>

        <div class="modal-botones">

            <button
                type="button"
                class="btn-cancelar"
                onclick="cerrarModalAccesoManual()"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="btn-guardar"
                id="btnAutorizarManual"
                onclick="autorizarAccesoManual()"
            >
                Tomar foto y autorizar
            </button>

        </div>

    </div>

</div>

<!-- ==================================================
     NUEVO: MODAL CONTROL BARRERA
================================================== -->

<div
    id="modalControlBarrera"
    class="modal"
>

    <div class="modal-contenido">

        <div
            id="iconoModalBarrera"
            class="icono-barrera"
        >
            🔓
        </div>

        <h3 id="tituloModalBarrera">
            Mantener barrera abierta
        </h3>

        <p
            id="descripcionModalBarrera"
            class="descripcion-modal"
        >
            Esta opción mantendrá la barrera abierta
            permanentemente hasta que se restablezca
            el modo automático.
        </p>

        <div
            id="advertenciaModalBarrera"
            class="advertencia-barrera"
        >
            ⚠ Mientras este modo esté activo,
            la barrera no se cerrará automáticamente
            después del paso de los vehículos.
        </div>

        <div class="campo">

            <label for="responsableBarrera">
                Responsable
            </label>

            <input
                type="text"
                id="responsableBarrera"
                placeholder="Nombre del responsable"
                maxlength="100"
            >

            <small>
                Se registrará quién solicitó el cambio
                de modo.
            </small>

        </div>

        <div class="modal-botones">

            <button
                type="button"
                class="btn-cancelar"
                onclick="cerrarModalControlBarrera()"
            >
                Cancelar
            </button>

            <button
                type="button"
                id="btnConfirmarControlBarrera"
                class="btn-peligro"
                onclick="confirmarControlBarrera()"
            >
                Mantener abierta
            </button>

        </div>

    </div>

</div>

<script>

let datosGlobal = [];
let registroSeleccionado = null;

/*
 * Estado del sistema de barrera.
 *
 * AUTO
 * HOLD_OPEN
 */
let barrierMode = "AUTO";

let barrierRequestInProgress = false;

// =====================================================
// HORA
// =====================================================

function actualizarHora() {
    document.getElementById("hora").innerText =
        new Date().toLocaleString("es-PY");
}

actualizarHora();

setInterval(
    actualizarHora,
    1000
);

// =====================================================
// MENSAJES
// =====================================================

function mostrarMensaje(
    texto,
    tipo
) {
    const mensaje =
        document.getElementById(
            "mensaje"
        );

    mensaje.innerText =
        texto;

    mensaje.className =
        `mensaje ${tipo}`;

    setTimeout(() => {
        mensaje.className =
            "mensaje";

        mensaje.innerText =
            "";
    }, 6000);
}

// =====================================================
// FECHAS
// =====================================================

function parseFechaMySQL(
    fechaTexto
) {
    if (!fechaTexto) {
        return null;
    }

    const partes =
        fechaTexto
            .trim()
            .split(" ");

    const fecha =
        partes[0]
            .split("-");

    const hora =
        partes[1]
            ? partes[1].split(":")
            : ["0", "0", "0"];

    if (
        fecha.length !== 3
    ) {
        return null;
    }

    return new Date(
        Number(fecha[0]),
        Number(fecha[1]) - 1,
        Number(fecha[2]),
        Number(hora[0] || 0),
        Number(hora[1] || 0),
        Number(hora[2] || 0)
    );
}

function esMismoDia(
    fecha1,
    fecha2
) {
    return (
        fecha1.getFullYear() ===
            fecha2.getFullYear() &&

        fecha1.getMonth() ===
            fecha2.getMonth() &&

        fecha1.getDate() ===
            fecha2.getDate()
    );
}

function perteneceAlPeriodo(
    fechaTexto,
    periodo
) {
    const fecha =
        parseFechaMySQL(
            fechaTexto
        );

    if (!fecha) {
        return false;
    }

    const ahora =
        new Date();

    if (
        periodo === "todo"
    ) {
        return true;
    }

    if (
        periodo === "hoy"
    ) {
        return esMismoDia(
            fecha,
            ahora
        );
    }

    if (
        periodo === "semana"
    ) {
        const inicio =
            new Date(
                ahora.getFullYear(),
                ahora.getMonth(),
                ahora.getDate() - 6,
                0,
                0,
                0
            );

        return (
            fecha >= inicio &&
            fecha <= ahora
        );
    }

    if (
        periodo === "mes"
    ) {
        return (
            fecha.getFullYear() ===
                ahora.getFullYear() &&

            fecha.getMonth() ===
                ahora.getMonth()
        );
    }

    return true;
}

// =====================================================
// ESCAPAR TEXTO
// =====================================================

function escaparHTML(
    texto
) {
    if (
        texto === null ||
        texto === undefined ||
        texto === ""
    ) {
        return "-";
    }

    const div =
        document.createElement(
            "div"
        );

    div.innerText =
        texto;

    return div.innerHTML;
}

function escaparAtributo(
    texto
) {
    return String(
        texto ?? ""
    )
        .replace(
            /\\/g,
            "\\\\"
        )
        .replace(
            /'/g,
            "\\'"
        )
        .replace(
            /\r/g,
            ""
        )
        .replace(
            /\n/g,
            " "
        );
}

// =====================================================
// GRUPOS
// =====================================================

function nombreGrupo(
    grupo
) {
    const grupos = {

        MOVIL_ANDE:
            "Móvil ANDE",

        FUNCIONARIO_ANDE:
            "Móvil de funcionario de ANDE",

        CONTRATISTA:
            "Móvil de contratista",

        PARTICULAR:
            "Móvil particular",

        PENDIENTE_REVISION:
            "Pendiente de revisión"
    };

    return (
        grupos[grupo] ||
        grupo ||
        "Sin información"
    );
}

// =====================================================
// CARGAR REGISTROS
// =====================================================

async function cargarDatos() {

    try {

        const respuesta =
            await fetch(
                "get_logs.php",
                {
                    cache:
                        "no-store"
                }
            );

        if (
            !respuesta.ok
        ) {
            throw new Error(
                "No se pudieron cargar los registros."
            );
        }

        const data =
            await respuesta.json();

        if (
            !Array.isArray(data)
        ) {
            throw new Error(
                data.mensaje ||
                "Respuesta inválida del servidor."
            );
        }

        datosGlobal =
            data;

        actualizarStats(
            data
        );

        aplicarFiltros();

    }
    catch (error) {

        console.error(
            error
        );

        document.getElementById(
            "tabla"
        ).innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="sin-resultados"
                >
                    Error al cargar los registros.
                </td>
            </tr>
        `;
    }
}

// =====================================================
// ESTADÍSTICAS
// =====================================================

function actualizarStats(
    data
) {
    const ahora =
        new Date();

    const registrosHoy =
        data.filter(
            row => {

                const fecha =
                    parseFechaMySQL(
                        row.access_time
                    );

                return (
                    fecha &&
                    esMismoDia(
                        fecha,
                        ahora
                    )
                );
            }
        );

    const permitidosHoy =
        registrosHoy.filter(
            row =>
                row.access_status ===
                "ACCESS_GRANTED"
        ).length;

    const pendientes =
        data.filter(
            row =>
                row.vehicle_group ===
                "PENDIENTE_REVISION"
        ).length;

    document.getElementById(
        "totalGeneral"
    ).innerText =
        data.length;

    document.getElementById(
        "totalHoy"
    ).innerText =
        registrosHoy.length;

    document.getElementById(
        "grantedHoy"
    ).innerText =
        permitidosHoy;

    document.getElementById(
        "pendientes"
    ).innerText =
        pendientes;
}

// =====================================================
// FILTROS
// =====================================================

function aplicarFiltros() {

    const busqueda =
        document
            .getElementById(
                "busqueda"
            )
            .value
            .trim()
            .toLowerCase();

    const grupo =
        document
            .getElementById(
                "grupo"
            )
            .value;

    const periodo =
        document
            .getElementById(
                "periodo"
            )
            .value;

    const filtrado =
        datosGlobal.filter(
            row => {

                const texto = `
                    ${row.card_uid || ""}
                    ${row.owner_name || ""}
                    ${row.plate_number || ""}
                    ${row.visitor_name || ""}
                    ${row.authorized_by || ""}
                `.toLowerCase();

                const coincideBusqueda =
                    texto.includes(
                        busqueda
                    );

                const coincideGrupo =
                    grupo === "todo" ||
                    row.vehicle_group ===
                        grupo;

                const coincidePeriodo =
                    perteneceAlPeriodo(
                        row.access_time,
                        periodo
                    );

                return (
                    coincideBusqueda &&
                    coincideGrupo &&
                    coincidePeriodo
                );
            }
        );

    renderTabla(
        filtrado
    );

    document.getElementById(
        "resultadoFiltro"
    ).innerText =
        `${filtrado.length} registro(s) mostrado(s)`;
}

// =====================================================
// TABLA
// =====================================================

function renderTabla(
    data
) {
    const tbody =
        document.getElementById(
            "tabla"
        );

    tbody.innerHTML =
        "";

    if (
        data.length === 0
    ) {
        tbody.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="sin-resultados"
                >
                    No se encontraron registros.
                </td>
            </tr>
        `;

        return;
    }

    data.forEach(
        row => {

            let claseEstado =
                "";

            if (
                row.access_status ===
                "ACCESS_GRANTED"
            ) {
                claseEstado =
                    "granted";
            }
            else if (
                row.access_status ===
                "ACCESS_DENIED"
            ) {
                claseEstado =
                    "denied";
            }
            else {
                claseEstado =
                    "system-error";
            }

            const claseGrupo =
                row.vehicle_group ===
                "PENDIENTE_REVISION"
                    ? "pendiente"
                    : row.group_source ===
                        "MANUAL"
                        ? "manual"
                        : "";

            const textoBoton =
                row.vehicle_group ===
                "PENDIENTE_REVISION"
                    ? "Clasificar"
                    : "Corregir";

            const dueñoMostrado =
                row.visitor_name ||
                row.owner_name;

            tbody.innerHTML += `
                <tr>

                    <td>
                        ${escaparHTML(
                            row.card_uid
                        )}
                    </td>

                    <td>
                        ${escaparHTML(
                            dueñoMostrado
                        )}
                    </td>

                    <td>
                        ${escaparHTML(
                            row.plate_number
                        )}
                    </td>

                    <td class="${claseGrupo}">
                        ${escaparHTML(
                            nombreGrupo(
                                row.vehicle_group
                            )
                        )}
                    </td>

                    <td class="${claseEstado}">
                        ${escaparHTML(
                            row.access_status
                        )}
                    </td>

                    <td>
                        ${escaparHTML(
                            row.access_time
                        )}
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn-clasificar"
                            onclick="abrirModalClasificar(
                                ${Number(row.id)},
                                '${escaparAtributo(
                                    row.plate_number
                                )}',
                                '${escaparAtributo(
                                    row.vehicle_group
                                )}',
                                '${escaparAtributo(
                                    row.reviewed_by
                                )}',
                                '${escaparAtributo(
                                    row.group_source
                                )}'
                            )"
                        >
                            ${textoBoton}
                        </button>
                    </td>

                </tr>
            `;
        }
    );
}

// =====================================================
// MODAL CLASIFICAR
// =====================================================

function abrirModalClasificar(
    id,
    matricula,
    grupoActual,
    revisadoPor,
    fuenteGrupo
) {
    registroSeleccionado =
        id;

    const esCorreccion =
        fuenteGrupo === "MANUAL" ||
        (
            grupoActual !==
                "PENDIENTE_REVISION" &&
            grupoActual !==
                ""
        );

    document.getElementById(
        "tituloModal"
    ).innerText =
        esCorreccion
            ? "Corregir clasificación"
            : "Clasificar registro";

    document.getElementById(
        "labelResponsable"
    ).innerText =
        esCorreccion
            ? "Corregido por"
            : "Clasificado por";

    document.getElementById(
        "registroMatricula"
    ).value =
        matricula || "";

    document.getElementById(
        "grupoManual"
    ).value =
        grupoActual ===
        "PENDIENTE_REVISION"
            ? ""
            : grupoActual;

    document.getElementById(
        "revisadoPor"
    ).value =
        revisadoPor || "";

    document
        .getElementById(
            "modalClasificar"
        )
        .classList
        .add(
            "abierto"
        );
}

function cerrarModalClasificar() {

    registroSeleccionado =
        null;

    document
        .getElementById(
            "modalClasificar"
        )
        .classList
        .remove(
            "abierto"
        );
}

async function guardarClasificacion() {

    const grupo =
        document.getElementById(
            "grupoManual"
        ).value;

    const revisadoPor =
        document
            .getElementById(
                "revisadoPor"
            )
            .value
            .trim();

    if (
        !registroSeleccionado
    ) {
        mostrarMensaje(
            "No se seleccionó un registro.",
            "error"
        );

        return;
    }

    if (!grupo) {
        mostrarMensaje(
            "Seleccione el grupo correcto.",
            "error"
        );

        return;
    }

    if (!revisadoPor) {
        mostrarMensaje(
            "Ingrese el nombre del responsable.",
            "error"
        );

        return;
    }

    try {

        const respuesta =
            await fetch(
                "update_group.php",
                {
                    method:
                        "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            id:
                                registroSeleccionado,

                            grupo:
                                grupo,

                            revisado_por:
                                revisadoPor
                        })
                }
            );

        const texto =
            await respuesta.text();

        let resultado;

        try {
            resultado =
                JSON.parse(
                    texto
                );
        }
        catch {

            console.error(
                "Respuesta del servidor:",
                texto
            );

            throw new Error(
                "El servidor devolvió una respuesta inválida."
            );
        }

        if (
            !respuesta.ok ||
            resultado.ok ===
                false
        ) {
            throw new Error(
                resultado.mensaje ||
                "No se pudo actualizar."
            );
        }

        cerrarModalClasificar();

        mostrarMensaje(
            resultado.mensaje ||
            "Registro actualizado correctamente.",
            "exito"
        );

        await cargarDatos();

    }
    catch (error) {

        console.error(
            error
        );

        mostrarMensaje(
            error.message,
            "error"
        );
    }
}

// =====================================================
// MODAL ACCESO MANUAL
// =====================================================

function abrirModalAccesoManual() {

    limpiarFormularioAccesoManual();

    document
        .getElementById(
            "modalAccesoManual"
        )
        .classList
        .add(
            "abierto"
        );

    document
        .getElementById(
            "visitanteManual"
        )
        .focus();
}

function cerrarModalAccesoManual() {

    document
        .getElementById(
            "modalAccesoManual"
        )
        .classList
        .remove(
            "abierto"
        );
}

function limpiarFormularioAccesoManual() {

    document.getElementById(
        "visitanteManual"
    ).value = "";

    document.getElementById(
        "personaVisitadaManual"
    ).value = "";

    document.getElementById(
        "matriculaManual"
    ).value = "";

    document.getElementById(
        "grupoVehiculoManual"
    ).value = "";

    document.getElementById(
        "motivoManual"
    ).value = "";

    document.getElementById(
        "responsableManual"
    ).value = "";
}

// =====================================================
// AUTORIZAR ACCESO MANUAL
// =====================================================

async function autorizarAccesoManual() {

    const visitante =
        document
            .getElementById(
                "visitanteManual"
            )
            .value
            .trim();

    const personaVisitada =
        document
            .getElementById(
                "personaVisitadaManual"
            )
            .value
            .trim();

    const matricula =
        document
            .getElementById(
                "matriculaManual"
            )
            .value
            .trim()
            .toUpperCase()
            .replace(
                /\s+/g,
                ""
            );

    const grupo =
        document
            .getElementById(
                "grupoVehiculoManual"
            )
            .value;

    const motivo =
        document
            .getElementById(
                "motivoManual"
            )
            .value
            .trim();

    const responsable =
        document
            .getElementById(
                "responsableManual"
            )
            .value
            .trim();

    if (!visitante) {

        mostrarMensaje(
            "Ingresá el nombre del visitante.",
            "error"
        );

        return;
    }

    if (!grupo) {

        mostrarMensaje(
            "Seleccioná el grupo del vehículo.",
            "error"
        );

        return;
    }

    if (!motivo) {

        mostrarMensaje(
            "Ingresá el motivo del ingreso.",
            "error"
        );

        return;
    }

    if (!responsable) {

        mostrarMensaje(
            "Ingresá el nombre de quien autoriza.",
            "error"
        );

        return;
    }

    const confirmar =
        confirm(
            "¿Confirmás la apertura manual de la barrera?\n\n" +
            "Visitante: " +
            visitante +
            "\n" +

            "Matrícula: " +
            (
                matricula ||
                "No visible"
            ) +
            "\n" +

            "Motivo: " +
            motivo
        );

    if (!confirmar) {
        return;
    }

    const boton =
        document.getElementById(
            "btnAutorizarManual"
        );

    boton.disabled =
        true;

    boton.innerText =
        "Generando orden...";

    try {

        const respuesta =
            await fetch(
                "manual_access.php",
                {
                    method:
                        "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            visitor_name:
                                visitante,

                            visited_person:
                                personaVisitada,

                            reason:
                                motivo,

                            plate_number:
                                matricula,

                            vehicle_group:
                                grupo,

                            requested_by:
                                responsable
                        })
                }
            );

        const texto =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(
                    texto
                );

        }
        catch {

            console.error(
                "Respuesta manual_access.php:",
                texto
            );

            throw new Error(
                "El servidor devolvió una respuesta inválida."
            );
        }

        if (
            !respuesta.ok ||
            resultado.ok ===
                false
        ) {

            throw new Error(
                resultado.mensaje ||
                "No se pudo generar la apertura manual."
            );
        }

        cerrarModalAccesoManual();

        mostrarMensaje(
            resultado.mensaje ||
            "Orden manual enviada correctamente.",
            "exito"
        );

        await cargarDatos();

    }
    catch (error) {

        console.error(
            error
        );

        mostrarMensaje(
            error.message,
            "error"
        );

    }
    finally {

        boton.disabled =
            false;

        boton.innerText =
            "Tomar foto y autorizar";
    }
}

// =====================================================
// NUEVO:
// CONSULTAR ESTADO DE BARRERA
// =====================================================

async function cargarEstadoBarrera() {

    try {

        const respuesta =
            await fetch(
                "barrier_status.php",
                {
                    cache:
                        "no-store"
                }
            );

        if (
            !respuesta.ok
        ) {
            throw new Error(
                "No se pudo consultar el estado de la barrera."
            );
        }

        const data =
            await respuesta.json();

        if (
            data.ok === false
        ) {
            throw new Error(
                data.error ||
                "Estado inválido."
            );
        }

        barrierMode =
            data.mode ||
            "AUTO";

        actualizarInterfazBarrera();

    }
    catch (error) {

        console.error(
            "Error barrier_status.php:",
            error
        );

        const estado =
            document.getElementById(
                "estadoBarreraSidebar"
            );

        estado.className =
            "estado-barrera error";

        estado.innerText =
            "No se pudo consultar el estado";
    }
}

// =====================================================
// ACTUALIZAR INTERFAZ BARRERA
// =====================================================

function actualizarInterfazBarrera() {

    const boton =
        document.getElementById(
            "btnControlBarrera"
        );

    const estado =
        document.getElementById(
            "estadoBarreraSidebar"
        );

    const alerta =
        document.getElementById(
            "alertaBarreraAbierta"
        );

    if (
        barrierMode ===
        "HOLD_OPEN"
    ) {

        boton.innerText =
            "🔒 Restablecer modo automático";

        boton.classList.add(
            "modo-abierto"
        );

        estado.className =
            "estado-barrera abierta";

        estado.innerText =
            "Barrera abierta permanentemente";

        alerta.classList.add(
            "visible"
        );
    }
    else {

        boton.innerText =
            "🔓 Mantener barrera abierta";

        boton.classList.remove(
            "modo-abierto"
        );

        estado.className =
            "estado-barrera auto";

        estado.innerText =
            "Modo automático activo";

        alerta.classList.remove(
            "visible"
        );
    }
}

// =====================================================
// MODAL CONTROL BARRERA
// =====================================================

function abrirModalControlBarrera() {

    const titulo =
        document.getElementById(
            "tituloModalBarrera"
        );

    const descripcion =
        document.getElementById(
            "descripcionModalBarrera"
        );

    const icono =
        document.getElementById(
            "iconoModalBarrera"
        );

    const boton =
        document.getElementById(
            "btnConfirmarControlBarrera"
        );

    const responsable =
        document.getElementById(
            "responsableBarrera"
        );

    responsable.value =
        "";

    if (
        barrierMode ===
        "HOLD_OPEN"
    ) {

        icono.innerText =
            "🔒";

        titulo.innerText =
            "Restablecer modo automático";

        descripcion.innerText =
            "Esta acción cerrará la barrera y restablecerá el funcionamiento automático mediante RFID y matrícula.";

        boton.innerText =
            "Restablecer automático";

        boton.className =
            "btn-guardar";

    }
    else {

        icono.innerText =
            "🔓";

        titulo.innerText =
            "Mantener barrera abierta";

        descripcion.innerText =
            "Esta opción mantendrá la barrera abierta permanentemente hasta que se restablezca el modo automático.";

        boton.innerText =
            "Mantener abierta";

        boton.className =
            "btn-peligro";
    }

    document
        .getElementById(
            "modalControlBarrera"
        )
        .classList
        .add(
            "abierto"
        );

    setTimeout(
        () => {
            responsable.focus();
        },
        100
    );
}

function cerrarModalControlBarrera() {

    document
        .getElementById(
            "modalControlBarrera"
        )
        .classList
        .remove(
            "abierto"
        );
}

// =====================================================
// CONFIRMAR CONTROL DE BARRERA
// =====================================================

async function confirmarControlBarrera() {

    if (
        barrierRequestInProgress
    ) {
        return;
    }

    const responsable =
        document
            .getElementById(
                "responsableBarrera"
            )
            .value
            .trim();

    if (!responsable) {

        mostrarMensaje(
            "Ingresá el nombre del responsable.",
            "error"
        );

        return;
    }

    const action =
        barrierMode ===
            "HOLD_OPEN"
            ? "RESUME_AUTO"
            : "HOLD_OPEN";

    let mensajeConfirmacion;

    if (
        action ===
        "HOLD_OPEN"
    ) {

        mensajeConfirmacion =
            "¿Confirmás mantener la barrera abierta permanentemente?\n\n" +
            "El cierre automático quedará suspendido.";

    }
    else {

        mensajeConfirmacion =
            "¿Confirmás restablecer el modo automático?\n\n" +
            "La barrera recibirá la orden de cierre.";
    }

    const confirmar =
        confirm(
            mensajeConfirmacion
        );

    if (!confirmar) {
        return;
    }

    await enviarComandoBarrera(
        action,
        responsable
    );
}

// =====================================================
// RESTABLECER DESDE ALERTA
// =====================================================

function solicitarRestablecerAutomatico() {

    if (
        barrierMode !==
        "HOLD_OPEN"
    ) {
        return;
    }

    abrirModalControlBarrera();
}

// =====================================================
// ENVIAR COMANDO AL SERVIDOR
// =====================================================

async function enviarComandoBarrera(
    action,
    responsable
) {

    barrierRequestInProgress =
        true;

    const botonSidebar =
        document.getElementById(
            "btnControlBarrera"
        );

    const botonModal =
        document.getElementById(
            "btnConfirmarControlBarrera"
        );

    botonSidebar.disabled =
        true;

    botonModal.disabled =
        true;

    botonModal.innerText =
        "Enviando orden...";

    try {

        const respuesta =
            await fetch(
                "barrier_control.php",
                {
                    method:
                        "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            action:
                                action,

                            requested_by:
                                responsable
                        })
                }
            );

        const texto =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(
                    texto
                );

        }
        catch {

            console.error(
                "Respuesta barrier_control.php:",
                texto
            );

            throw new Error(
                "El servidor devolvió una respuesta inválida."
            );
        }

        if (
            !respuesta.ok ||
            resultado.ok ===
                false
        ) {

            throw new Error(
                resultado.mensaje ||
                resultado.error ||
                "No se pudo enviar la orden a la barrera."
            );
        }

        barrierMode =
            resultado.mode ||
            (
                action ===
                    "HOLD_OPEN"
                    ? "HOLD_OPEN"
                    : "AUTO"
            );

        actualizarInterfazBarrera();

        cerrarModalControlBarrera();

        if (
            action ===
            "HOLD_OPEN"
        ) {

            mostrarMensaje(
                "Orden enviada. La barrera permanecerá abierta.",
                "info"
            );

        }
        else {

            mostrarMensaje(
                "Orden enviada. Se restablecerá el modo automático.",
                "exito"
            );
        }

        /*
         * Volvemos a consultar después de unos segundos
         * para sincronizar la interfaz con la base de datos.
         */
        setTimeout(
            cargarEstadoBarrera,
            2000
        );

    }
    catch (error) {

        console.error(
            error
        );

        mostrarMensaje(
            error.message,
            "error"
        );

    }
    finally {

        barrierRequestInProgress =
            false;

        botonSidebar.disabled =
            false;

        botonModal.disabled =
            false;

        actualizarInterfazBarrera();
    }
}

// =====================================================
// EVENTOS
// =====================================================

document
    .getElementById(
        "busqueda"
    )
    .addEventListener(
        "input",
        aplicarFiltros
    );

document
    .getElementById(
        "grupo"
    )
    .addEventListener(
        "change",
        aplicarFiltros
    );

document
    .getElementById(
        "periodo"
    )
    .addEventListener(
        "change",
        aplicarFiltros
    );

// =====================================================
// CERRAR MODAL CLASIFICAR
// =====================================================

document
    .getElementById(
        "modalClasificar"
    )
    .addEventListener(
        "click",
        event => {

            if (
                event.target.id ===
                "modalClasificar"
            ) {
                cerrarModalClasificar();
            }
        }
    );

// =====================================================
// CERRAR MODAL ACCESO MANUAL
// =====================================================

document
    .getElementById(
        "modalAccesoManual"
    )
    .addEventListener(
        "click",
        event => {

            if (
                event.target.id ===
                "modalAccesoManual"
            ) {
                cerrarModalAccesoManual();
            }
        }
    );

// =====================================================
// CERRAR MODAL BARRERA
// =====================================================

document
    .getElementById(
        "modalControlBarrera"
    )
    .addEventListener(
        "click",
        event => {

            if (
                event.target.id ===
                "modalControlBarrera"
            ) {
                cerrarModalControlBarrera();
            }
        }
    );

// =====================================================
// ESCAPE
// =====================================================

document.addEventListener(
    "keydown",
    event => {

        if (
            event.key ===
            "Escape"
        ) {
            cerrarModalClasificar();

            cerrarModalAccesoManual();

            cerrarModalControlBarrera();
        }
    }
);

// =====================================================
// ACTUALIZACIONES PERIÓDICAS
// =====================================================

// Logs cada 3 segundos
setInterval(
    cargarDatos,
    3000
);

// Estado barrera cada 3 segundos
setInterval(
    cargarEstadoBarrera,
    3000
);

// =====================================================
// PRIMERA CARGA
// =====================================================

cargarDatos();

cargarEstadoBarrera();

</script>

</body>
</html>
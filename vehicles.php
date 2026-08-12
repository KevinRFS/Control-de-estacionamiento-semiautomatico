<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Vehículos</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            color: #0f172a;
        }

        .contenedor {
            max-width: 1000px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        h2,
        h3 {
            margin-top: 0;
        }

        .formulario {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #2563eb;
        }

        button {
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-guardar {
            background: #2563eb;
            color: white;
        }

        .btn-guardar:hover {
            background: #1d4ed8;
        }

        .btn-editar {
            background: #f59e0b;
            color: white;
            padding: 7px 12px;
            margin-right: 6px;
        }

        .btn-editar:hover {
            background: #d97706;
        }

        .btn-eliminar {
            background: #dc2626;
            color: white;
            padding: 7px 12px;
        }

        .btn-eliminar:hover {
            background: #b91c1c;
        }

        .mensaje {
            display: none;
            margin-top: 15px;
            padding: 10px;
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

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }

        th {
            background: #0f172a;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .estado-activo {
            color: #166534;
            font-weight: bold;
        }

        .estado-inactivo {
            color: #b91c1c;
            font-weight: bold;
        }

        .sin-resultados {
            padding: 20px;
            color: #64748b;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }

        .modal.abierto {
            display: flex;
        }

        .modal-contenido {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        }

        .modal-contenido h3 {
            margin-bottom: 18px;
        }

        .modal-formulario {
            display: grid;
            gap: 15px;
        }

        .modal-botones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-cancelar {
            background: #64748b;
            color: white;
        }

        .btn-actualizar {
            background: #16a34a;
            color: white;
        }

        @media (max-width: 750px) {
            body {
                padding: 15px;
            }

            .formulario {
                grid-template-columns: 1fr;
            }

            .btn-editar,
            .btn-eliminar {
                display: block;
                width: 100%;
                margin: 4px 0;
            }
        }
    </style>
</head>

<body>

<div class="contenedor">

    <div class="card">

        <h2>Registrar vehículo</h2>

        <div class="formulario">

            <div class="campo">
                <label for="patente">Matrícula</label>

                <input
                    type="text"
                    id="patente"
                    placeholder="Ejemplo: ABC123"
                    maxlength="20"
                >
            </div>

            <div class="campo">
                <label for="grupo">Grupo del vehículo</label>

                <select id="grupo">
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

            <button
                class="btn-guardar"
                type="button"
                onclick="agregar()"
            >
                Guardar
            </button>

        </div>

        <div id="mensaje" class="mensaje"></div>

    </div>

    <div class="card">

        <h3>Lista de vehículos</h3>

        <div class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Grupo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody id="tabla"></tbody>
            </table>

        </div>

    </div>

</div>

<div id="modalEditar" class="modal">

    <div class="modal-contenido">

        <h3>Editar vehículo</h3>

        <div class="modal-formulario">

            <div class="campo">
                <label for="editarPatente">Matrícula</label>

                <input
                    type="text"
                    id="editarPatente"
                    readonly
                >
            </div>

            <div class="campo">
                <label for="editarGrupo">Grupo del vehículo</label>

                <select id="editarGrupo">
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
                <label for="editarEstado">Estado</label>

                <select id="editarEstado">
                    <option value="ACTIVE">
                        Activo
                    </option>

                    <option value="INACTIVE">
                        Inactivo
                    </option>
                </select>
            </div>

            <div class="modal-botones">

                <button
                    type="button"
                    class="btn-cancelar"
                    onclick="cerrarModal()"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn-actualizar"
                    onclick="guardarEdicion()"
                >
                    Actualizar
                </button>

            </div>

        </div>

    </div>

</div>

<script>

function mostrarMensaje(texto, tipo) {
    const mensaje = document.getElementById("mensaje");

    mensaje.innerText = texto;
    mensaje.className = `mensaje ${tipo}`;

    setTimeout(() => {
        mensaje.className = "mensaje";
        mensaje.innerText = "";
    }, 4000);
}

function escaparHTML(texto) {
    if (texto === null || texto === undefined) {
        return "-";
    }

    const div = document.createElement("div");
    div.innerText = texto;

    return div.innerHTML;
}

function escaparAtributo(texto) {
    return String(texto)
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'");
}

function nombreGrupo(grupo) {
    const grupos = {
        MOVIL_ANDE: "Móvil ANDE",
        FUNCIONARIO_ANDE:
            "Móvil de funcionario de ANDE",
        CONTRATISTA:
            "Móvil de contratista",
        PARTICULAR:
            "Móvil particular"
    };

    return grupos[grupo] || grupo || "Sin grupo";
}

function nombreEstado(estado) {
    if (estado === "ACTIVE") {
        return "Activo";
    }

    if (estado === "INACTIVE") {
        return "Inactivo";
    }

    return estado || "-";
}

async function cargar() {
    const tabla = document.getElementById("tabla");

    try {
        const respuesta = await fetch(
            "get_vehicles.php",
            {
                cache: "no-store"
            }
        );

        if (!respuesta.ok) {
            throw new Error(
                "No se pudo obtener la lista de vehículos."
            );
        }

        const data = await respuesta.json();

        tabla.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            tabla.innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        class="sin-resultados"
                    >
                        No hay vehículos registrados.
                    </td>
                </tr>
            `;

            return;
        }

        data.forEach(v => {
            const claseEstado =
                v.access_status === "ACTIVE"
                    ? "estado-activo"
                    : "estado-inactivo";

            tabla.innerHTML += `
                <tr>
                    <td>
                        ${escaparHTML(v.plate_number)}
                    </td>

                    <td>
                        ${escaparHTML(
                            nombreGrupo(v.vehicle_group)
                        )}
                    </td>

                    <td class="${claseEstado}">
                        ${escaparHTML(
                            nombreEstado(v.access_status)
                        )}
                    </td>

                    <td>
                        <button
                            class="btn-editar"
                            type="button"
                            onclick="abrirModal(
                                '${escaparAtributo(v.plate_number)}',
                                '${escaparAtributo(v.vehicle_group)}',
                                '${escaparAtributo(v.access_status)}'
                            )"
                        >
                            Editar
                        </button>

                        <button
                            class="btn-eliminar"
                            type="button"
                            onclick="eliminar(
                                '${escaparAtributo(v.plate_number)}'
                            )"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;
        });

    } catch (error) {
        console.error(error);

        tabla.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="sin-resultados"
                >
                    Error al cargar los vehículos.
                </td>
            </tr>
        `;
    }
}

async function agregar() {
    const patenteInput =
        document.getElementById("patente");

    const grupoInput =
        document.getElementById("grupo");

    const patente = patenteInput
        .value
        .trim()
        .toUpperCase()
        .replace(/\s+/g, "");

    const grupo = grupoInput.value;

    if (!patente) {
        mostrarMensaje(
            "Ingresá la matrícula del vehículo.",
            "error"
        );

        patenteInput.focus();
        return;
    }

    if (!grupo) {
        mostrarMensaje(
            "Seleccioná un grupo para el vehículo.",
            "error"
        );

        grupoInput.focus();
        return;
    }

    try {
        const respuesta = await fetch(
            "add_vehicle.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    patente: patente,
                    grupo: grupo
                })
            }
        );

        const texto = await respuesta.text();

        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            resultado = {
                ok: respuesta.ok,
                mensaje: texto
            };
        }

        if (!respuesta.ok || resultado.ok === false) {
            throw new Error(
                resultado.mensaje ||
                resultado.error ||
                "No se pudo registrar el vehículo."
            );
        }

        mostrarMensaje(
            resultado.mensaje ||
            "Vehículo registrado correctamente.",
            "exito"
        );

        patenteInput.value = "";
        grupoInput.value = "";

        await cargar();

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            "error"
        );
    }
}

function abrirModal(patente, grupo, estado) {
    document.getElementById("editarPatente").value =
        patente;

    document.getElementById("editarGrupo").value =
        grupo;

    document.getElementById("editarEstado").value =
        estado;

    document
        .getElementById("modalEditar")
        .classList
        .add("abierto");
}

function cerrarModal() {
    document
        .getElementById("modalEditar")
        .classList
        .remove("abierto");
}

async function guardarEdicion() {
    const patente =
        document.getElementById("editarPatente").value;

    const grupo =
        document.getElementById("editarGrupo").value;

    const estado =
        document.getElementById("editarEstado").value;

    try {
        const respuesta = await fetch(
            "update_vehicle.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    patente: patente,
                    grupo: grupo,
                    estado: estado
                })
            }
        );

        const texto = await respuesta.text();

        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            throw new Error(
                texto ||
                "El servidor devolvió una respuesta inválida."
            );
        }

        if (!respuesta.ok || resultado.ok === false) {
            throw new Error(
                resultado.mensaje ||
                resultado.error ||
                "No se pudo actualizar el vehículo."
            );
        }

        cerrarModal();

        mostrarMensaje(
            resultado.mensaje ||
            "Vehículo actualizado correctamente.",
            "exito"
        );

        await cargar();

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            "error"
        );
    }
}

async function eliminar(patente) {
    const confirmar = confirm(
        `¿Deseás eliminar el vehículo ${patente}?`
    );

    if (!confirmar) {
        return;
    }

    try {
        const respuesta = await fetch(
            "delete_vehicle.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    patente: patente
                })
            }
        );

        const texto = await respuesta.text();

        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            resultado = {
                ok: respuesta.ok,
                mensaje: texto
            };
        }

        if (!respuesta.ok || resultado.ok === false) {
            throw new Error(
                resultado.mensaje ||
                resultado.error ||
                "No se pudo eliminar el vehículo."
            );
        }

        mostrarMensaje(
            resultado.mensaje ||
            "Vehículo eliminado correctamente.",
            "exito"
        );

        await cargar();

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            "error"
        );
    }
}

document
    .getElementById("patente")
    .addEventListener("keydown", event => {
        if (event.key === "Enter") {
            agregar();
        }
    });

document
    .getElementById("modalEditar")
    .addEventListener("click", event => {
        if (event.target.id === "modalEditar") {
            cerrarModal();
        }
    });

cargar();

</script>

</body>
</html>
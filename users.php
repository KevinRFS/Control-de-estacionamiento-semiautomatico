<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Usuarios RFID</title>

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
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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
            padding: 7px 12px;
            margin-right: 6px;
            background: #f59e0b;
            color: white;
        }

        .btn-editar:hover {
            background: #d97706;
        }

        .btn-eliminar {
            padding: 7px 12px;
            background: #dc2626;
            color: white;
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
            max-width: 500px;
            padding: 24px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
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

        <h2>Registrar usuario RFID</h2>

        <div class="formulario">

            <div class="campo">
                <label for="uid">UID RFID</label>

                <input
                    type="text"
                    id="uid"
                    placeholder="Ejemplo: 4EF67606"
                    maxlength="50"
                >
            </div>

            <div class="campo">
                <label for="nombre">Nombre completo</label>

                <input
                    type="text"
                    id="nombre"
                    placeholder="Nombre y apellido"
                    maxlength="100"
                >
            </div>

            <button
                type="button"
                class="btn-guardar"
                onclick="agregar()"
            >
                Guardar
            </button>

        </div>

        <div id="mensaje" class="mensaje"></div>

    </div>

    <div class="card">

        <h3>Lista de usuarios</h3>

        <div class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody id="tabla"></tbody>
            </table>

        </div>

    </div>

</div>

<!-- MODAL DE EDICIÓN -->

<div id="modalEditar" class="modal">

    <div class="modal-contenido">

        <h3>Editar usuario RFID</h3>

        <div class="modal-formulario">

            <div class="campo">
                <label for="editarUid">UID RFID</label>

                <input
                    type="text"
                    id="editarUid"
                    readonly
                >
            </div>

            <div class="campo">
                <label for="editarNombre">Nombre completo</label>

                <input
                    type="text"
                    id="editarNombre"
                    maxlength="100"
                >
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
    if (texto === null || texto === undefined || texto === "") {
        return "-";
    }

    const div = document.createElement("div");
    div.innerText = texto;

    return div.innerHTML;
}

function escaparAtributo(texto) {
    return String(texto ?? "")
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'")
        .replace(/\r/g, "")
        .replace(/\n/g, " ");
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

// =====================================================
// CARGAR USUARIOS
// =====================================================

async function cargar() {
    const tabla = document.getElementById("tabla");

    try {
        const respuesta = await fetch(
            "get_users.php",
            {
                cache: "no-store"
            }
        );

        if (!respuesta.ok) {
            throw new Error(
                "No se pudo obtener la lista de usuarios."
            );
        }

        const data = await respuesta.json();

        tabla.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            tabla.innerHTML = `
                <tr>
                    <td
                        colspan="5"
                        class="sin-resultados"
                    >
                        No hay usuarios registrados.
                    </td>
                </tr>
            `;

            return;
        }

        data.forEach(usuario => {
            const claseEstado =
                usuario.access_status === "ACTIVE"
                    ? "estado-activo"
                    : "estado-inactivo";

            tabla.innerHTML += `
                <tr>
                    <td>
                        ${escaparHTML(usuario.card_uid)}
                    </td>

                    <td>
                        ${escaparHTML(usuario.owner_name)}
                    </td>

                    <td class="${claseEstado}">
                        ${escaparHTML(
                            nombreEstado(usuario.access_status)
                        )}
                    </td>

                    <td>
                        ${escaparHTML(usuario.last_access)}
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn-editar"
                            onclick="abrirModal(
                                '${escaparAtributo(usuario.card_uid)}',
                                '${escaparAtributo(usuario.owner_name)}',
                                '${escaparAtributo(usuario.access_status)}'
                            )"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            class="btn-eliminar"
                            onclick="eliminar(
                                '${escaparAtributo(usuario.card_uid)}'
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
                    colspan="5"
                    class="sin-resultados"
                >
                    Error al cargar los usuarios.
                </td>
            </tr>
        `;
    }
}

// =====================================================
// REGISTRAR USUARIO
// =====================================================

async function agregar() {
    const uidInput =
        document.getElementById("uid");

    const nombreInput =
        document.getElementById("nombre");

    const uid = uidInput
        .value
        .trim()
        .toUpperCase()
        .replace(/\s+/g, "");

    const nombre = nombreInput
        .value
        .trim();

    if (!uid) {
        mostrarMensaje(
            "Ingresá el UID de la tarjeta.",
            "error"
        );

        uidInput.focus();
        return;
    }

    if (!nombre) {
        mostrarMensaje(
            "Ingresá el nombre completo.",
            "error"
        );

        nombreInput.focus();
        return;
    }

    try {
        const respuesta = await fetch(
            "add_user.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    uid: uid,
                    nombre: nombre
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
                "No se pudo registrar el usuario."
            );
        }

        mostrarMensaje(
            resultado.mensaje ||
            "Usuario registrado correctamente.",
            "exito"
        );

        uidInput.value = "";
        nombreInput.value = "";

        await cargar();

    } catch (error) {
        console.error(error);

        mostrarMensaje(
            error.message,
            "error"
        );
    }
}

// =====================================================
// ABRIR Y CERRAR MODAL
// =====================================================

function abrirModal(uid, nombre, estado) {
    document.getElementById("editarUid").value =
        uid;

    document.getElementById("editarNombre").value =
        nombre;

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

// =====================================================
// GUARDAR EDICIÓN
// =====================================================

async function guardarEdicion() {
    const uid =
        document.getElementById("editarUid").value;

    const nombre = document
        .getElementById("editarNombre")
        .value
        .trim();

    const estado =
        document.getElementById("editarEstado").value;

    if (!nombre) {
        mostrarMensaje(
            "El nombre no puede quedar vacío.",
            "error"
        );

        return;
    }

    try {
        const respuesta = await fetch(
            "update_user.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    uid: uid,
                    nombre: nombre,
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
                "No se pudo actualizar el usuario."
            );
        }

        cerrarModal();

        mostrarMensaje(
            resultado.mensaje ||
            "Usuario actualizado correctamente.",
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

// =====================================================
// ELIMINAR USUARIO
// =====================================================

async function eliminar(uid) {
    const confirmar = confirm(
        `¿Deseás eliminar el usuario con UID ${uid}?`
    );

    if (!confirmar) {
        return;
    }

    try {
        const respuesta = await fetch(
            "delete_user.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    uid: uid
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
                "No se pudo eliminar el usuario."
            );
        }

        mostrarMensaje(
            resultado.mensaje ||
            "Usuario eliminado correctamente.",
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
    .getElementById("nombre")
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
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Perfil</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
        padding: 20px;
    }

    .perfil-container {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 30px;
        max-width: 1100px;
        margin: auto;
    }

    .perfil-header {
        display: flex;
        align-items: center;
        gap: 30px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }

    .perfil-header img {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 3px solid #ccc;
        object-fit: cover;
    }

    .perfil-header button {
        background: #e3e5e8;
        border: 1px solid #aaa;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: not-allowed;
        color: #666;
    }

    .perfil-header button:hover {
        background: #e3e5e8;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #444;
    }

    .form-group input {
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background: #f9f9f9;
    }

    .form-group input[readonly] {
        cursor: not-allowed;
        color: #555;
    }

    .tablas {
        display: flex;
        gap: 20px;
        margin-top: 30px;
    }

    .tabla {
        flex: 1;
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 15px;
        background: #fafafa;
    }

    .tabla h4 {
        margin-top: 0;
        font-size: 16px;
        color: #333;
        border-bottom: 1px solid #ccc;
        padding-bottom: 8px;
    }

    .tabla table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .tabla th, .tabla td {
        border-bottom: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-size: 14px;
    }

    .tabla-actions button {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        margin-right: 8px;
    }

    .tabla-actions button.edit { color: #0078d7; }
    .tabla-actions button.delete { color: #e74c3c; }

    .perfil-footer {
        text-align: right;
        margin-top: 30px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }

    .btn.cancelar {
        background: #ccc;
        margin-right: 10px;
    }

    .btn.guardar {
        background: #0078d7;
        color: white;
    }

    .btn.cancelar:hover {
        background: #b3b3b3;
    }

    .btn.guardar:hover {
        background: #005fa3;
    }

    @media (max-width: 900px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .tablas {
            flex-direction: column;
        }
    }
</style>
</head>
<body>

<div class="perfil-container">
    <div class="perfil-header">
        <img src="https://i.imgur.com/Lb9Wn9n.png" alt="Foto de perfil">
        <div>
            <button disabled>Cambiar imagen</button>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" value="Julio César" readonly>
        </div>
        <div class="form-group">
            <label>Apellido Paterno</label>
            <input type="text" value="Gómez" readonly>
        </div>
        <div class="form-group">
            <label>Apellido Materno</label>
            <input type="text" value="Burgos" readonly>
        </div>
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" value="admin" readonly>
        </div>
        <div class="form-group">
            <label>Fecha de nacimiento</label>
            <input type="date" value="1998-08-15" readonly>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <input type="text" value="Administrador" readonly>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <div style="display:flex;align-items:center;gap:5px;">
                <input type="password" value="********" readonly>
                <span style="font-size:18px;cursor:default;">👁️</span>
            </div>
        </div>
    </div>

    <div class="tablas">
        <div class="tabla">
            <h4>Correos Electrónicos</h4>
            <table>
                <thead>
                    <tr><th>Correo</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>julio@example.com</td>
                        <td class="tabla-actions">
                            <button class="edit">✏️</button>
                            <button class="delete">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="tabla">
            <h4>Números Telefónicos</h4>
            <table>
                <thead>
                    <tr><th>Número</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>+52 998 123 4567</td>
                        <td class="tabla-actions">
                            <button class="edit">✏️</button>
                            <button class="delete">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="perfil-footer">
        <button class="btn cancelar">CANCELAR</button>
        <button class="btn guardar">ACEPTAR CAMBIOS</button>
    </div>
</div>

</body>
</html>
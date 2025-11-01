<!-- Este es el segmento para la sección de gestión de usuarios -->
<div class="section-card">
    <h2 class="section-title">Gestión de Usuarios</h2>
    <table id="tablaUsuarios" class="display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Nombre Completo</th>
                <th>Correo Electrónico</th>
                <th>Número telefónico</th>                
                <th>Rol</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // La variable $users viene del AdminController, método showUsers()
            if (!empty($users)) {
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id_usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['nombres'] . ' ' . $user['apellido_p']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['correo']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['telefono']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['rol']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['estatus']) . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>

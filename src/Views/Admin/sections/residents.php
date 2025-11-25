<div class="content-header">
    <div class="header-text">
        <h1>Gestión de Residentes</h1>
        <p>Consulta la información de los residentes de la privada.</p>
    </div>
    <div class="header-actions">
        <button id="addResidentBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Nuevo
        </button>
    </div>
 </div>

 <div class="card-header">
        <h5>Listado de Residentes:</h5>        
    </div>
    
 <div class="section-card">  
     <div class="card-header filter-header" style="position: relative;">
    <div class="filter-container superposed">
        <label for="residentFilter">Mostrar:</label>
        <select id="residentFilter" name="residentFilter">
            <option value="owners" <?php echo ($currentFilter === 'owners') ? 'selected' : ''; ?>>Solo Propietarios</option>
            <option value="all" <?php echo ($currentFilter === 'all') ? 'selected' : ''; ?>>Todos</option>
        </select>
    </div>
</div>


     <table id="tablaResidentes" class="display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo</th>
                <th>Tipo</th>
                <th>Correos</th>
                <th>Teléfonos</th>
                <th>Casa</th>
                <th>Privada</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
     <tbody>
    <?php if (!empty($residents)): ?>
        <?php $contador = 1; ?>
        <?php foreach ($residents as $resident): ?>
            <tr>
                <td><?php echo $contador++; ?></td>
                <td><?php echo htmlspecialchars($resident['nombres'] . ' ' . $resident['apellido_p']); ?></td>
                <td><?php echo $resident['es_propietario'] ? '<span class="badge owner">Propietario</span>' : '<span class="badge resident">Residente</span>'; ?></td>
                <td>
                    <?php
                    $correos = !empty($resident['correos']) ? explode(', ', $resident['correos']) : [];
                    if (count($correos) === 1) {
                        echo '<a href="mailto:' . htmlspecialchars($correos[0]) . '">' . htmlspecialchars($correos[0]) . '</a>';
                    } elseif (count($correos) > 1) {
                        echo '<select onchange="window.location.href=\'mailto:\'+this.value">';
                        echo '<option>Ver correos...</option>';
                        foreach ($correos as $correo) {
                            echo '<option value="' . htmlspecialchars($correo) . '">' . htmlspecialchars($correo) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $telefonos = !empty($resident['telefonos']) ? explode(', ', $resident['telefonos']) : [];
                    if (count($telefonos) === 1) {
                        echo '<a href="tel:' . htmlspecialchars($telefonos[0]) . '">' . htmlspecialchars($telefonos[0]) . '</a>';
                    } elseif (count($telefonos) > 1) {
                        echo '<select onchange="window.location.href=\'tel:\'+this.value">';
                        echo '<option>Ver teléfonos...</option>';
                        foreach ($telefonos as $telefono) {
                            echo '<option value="' . htmlspecialchars($telefono) . '">' . htmlspecialchars($telefono) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($resident['num_casa']); ?></td>
                <td><?php echo htmlspecialchars($resident['privada_nombre']); ?></td>
                <td><?php echo htmlspecialchars($resident['estatus']); ?></td>
                <td>
                    <button class="btn-edit" data-id="<?php echo $resident['public_id']; ?>"><svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg></button>
                    <button class="btn-delete" data-id="<?php echo $resident['public_id']; ?>"><svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg></button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>

    </table>
</div>

<!-- MODAL UNIFICADO PARA AÑADIR RESIDENTE / RESIDENTE EXTRA -->
<div id="addResidentModal" class="modal-overlay">
    <div class="modal-content">

        <!-- ENCABEZADO GENERAL -->
        <div class="modal-header">
            <h5 id="addModalTitle">Añadir Residente</h5>
            <button id="closeAddModalBtn" class="modal-close">&times;</button>
        </div>

        <!-- CONTENEDOR -->
        <div class="modal-body">
            <!-- Selector universal de propietario -->
<div class="form-group form-group-radio">
    <label>¿Es propietario?</label>
    <div class="radio-group">
        <label for="propietario_si">
            <input type="radio" id="propietario_si" name="es_propietario" value="1"> Sí
        </label>

        <label for="propietario_no" style="margin-left: 10px;">
            <input type="radio" id="propietario_no" name="es_propietario" value="0" checked> No
        </label>
    </div>
</div>


            <!-- ========================================================= -->
            <!-- ==================== FORMULARIO PRINCIPAL ================ -->
            <!-- ========================================================= -->

            <form id="addResidentForm">

                <!-- Seccion de datos personales -->
                <h6 class="form-section-header">Información Personal</h6>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_nombres">Nombre(s)</label>
                        <input type="text" id="add_nombres" name="nombres" required>
                    </div>
                    <div class="form-group">
                        <label for="add_apellido_p">Apellido Paterno</label>
                        <input type="text" id="add_apellido_p" name="apellido_p" required>
                    </div>
                    <div class="form-group">
                        <label for="add_apellido_m">Apellido Materno</label>
                        <input type="text" id="add_apellido_m" name="apellido_m" required>
                    </div>
                    <div class="form-group">
                        <label for="add_fecha_nac">Fecha de Nacimiento</label>
                        <input type="date" id="add_fecha_nac" name="fecha_nac" required>
                    </div>
                </div>

                <!-- Seccion de credenciales -->
                <h6 class="form-section-header">Credenciales de Acceso</h6>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_username">Nombre de Usuario</label>
                        <input type="text" id="add_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="add_password">Contraseña</label>
                        <input type="password" id="add_password" name="password" required>
                        <small id="password_message" style="display:block; margin-top:5px;"></small>
                    </div>
                </div>

                <!-- Seccion de roles -->
                <h6 class="form-section-header">Asignaciones</h6>
                <div class="form-grid">

                    <div class="form-group">
                        <label for="add_privada">Privada Asignada</label>
                        <select id="add_privada" name="privada" required>
                            <option value="" disabled selected>Seleccionar privada...</option>
                            <?php if (!empty($Presidentes)): ?>
                                <?php foreach ($Presidentes as $item): ?>
                                    <option value="<?= htmlspecialchars($item['nombre']) ?>">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add_num_casa">Número de Casa</label>
                        <input type="text" id="add_num_casa" name="num_casa" required>
                    </div>

                    <div class="form-group">
                        <label for="add_estatus">Estatus</label>
                        <select id="add_estatus" name="estatus" required>
                            <option value="" disabled selected>Seleccionar estatus</option>
                            <?php foreach ($residentesEstados as $item): ?>
                                <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Seccion de contactos -->
                <h6 class="form-section-header">Información de Contacto</h6>
                <div class="form-grid-contact">
                    <div class="form-group">
                        <label for="add_email1">Correo Electrónico Principal</label>
                        <input type="email" id="add_email1" name="email1" required>
                    </div>
                    <div class="form-group">
                        <label for="add_email2">Correo Electrónico Secundario (Opcional)</label>
                        <input type="email" id="add_email2" name="email2">
                    </div>
                    <div class="form-group">
                        <label for="add_phone1">Teléfono Principal</label>
                        <input type="tel" id="add_phone1" name="phone1" required>
                        <span id="phone1_message"></span>
                    </div>
                    <div class="form-group">
                        <label for="add_phone2">Teléfono Secundario (Opcional)</label>
                        <input type="tel" id="add_phone2" name="phone2">
                        <span id="phone2_message"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelAddBtn" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Añadir Residente</button>
                </div>

            </form>

            <!-- ========================================================= -->
            <!-- ==================== FORMULARIO EXTRA ==================== -->
            <!-- ========================================================= -->

            <form id="addExtraForm" style="margin-top: 40px;">

                <!-- Seccion de datos personales -->
                <h6 class="form-section-header">Información Personal (Extra)</h6>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="extra_add_nombres">Nombre(s)</label>
                        <input type="text" id="extra_add_nombres" name="nombres" required>
                    </div>
                    <div class="form-group">
                        <label for="extra_add_apellido_p">Apellido Paterno</label>
                        <input type="text" id="extra_add_apellido_p" name="apellido_p" required>
                    </div>
                    <div class="form-group">
                        <label for="extra_add_apellido_m">Apellido Materno</label>
                        <input type="text" id="extra_add_apellido_m" name="apellido_m" required>
                    </div>
                </div>

                <!-- Seccion de roles -->
                <h6 class="form-section-header">Asignaciones</h6>
                <div class="form-grid">

                    <div class="form-group">
                        <label for="add_extra_num_casa">Número de Casa</label>
                        <input type="text" id="add_extra_num_casa" name="num_casa" required>
                    </div>

                    <div class="form-group">
                        <label for="extra_add_privada">Privada Asignada</label>
                        <select id="extra_add_privada" name="privada" required>
                            <option value="" disabled selected>Seleccionar privada...</option>
                            <?php if (!empty($Presidentes)): ?>
                                <?php foreach ($Presidentes as $item): ?>
                                    <option value="<?= htmlspecialchars($item['nombre']) ?>">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="extra_add_estatus">Estatus</label>
                        <select id="extra_add_estatus" name="estatus" required>
                            <option value="" disabled selected>Seleccionar estatus</option>
                            <?php foreach ($residentesEstados as $item): ?>
                                <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="propietario">Propietario asignado</label>
                    <input type="text" id="add_extra_propietario" readonly>
                </div>

                <!-- Seccion de contactos -->
                <h6 class="form-section-header">Información de Contacto</h6>
                <div class="form-grid-contact">
                    <div class="form-group">
                        <label for="extra_add_email1">Correo Electrónico Principal</label>
                        <input type="email" id="extra_add_email1" name="email1" required>
                    </div>
                    <div class="form-group">
                        <label for="extra_add_email2">Correo Electrónico Secundario (Opcional)</label>
                        <input type="email" id="extra_add_email2" name="email2">
                    </div>
                    <div class="form-group">
                        <label for="extra_add_phone1">Teléfono Principal</label>
                        <input type="tel" id="extra_add_phone1" name="phone1" required>
                        <span id="phone1_messageextra"></span>
                    </div>
                    <div class="form-group">
                        <label for="extra_add_phone2">Teléfono Secundario (Opcional)</label>
                        <input type="tel" id="extra_add_phone2" name="phone2">
                        <span id="phone2_messageextra"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelExtraBtn" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Añadir Residente</button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- MODAL DE EDICION DE DATOS-->
<div id="residentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="modalTitle">Editar Residente</h5>
            <button id="closeModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="residentForm">
            <input type="hidden" id="residentId" name="id_info">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombreCompleto">Nombre Completo</label>
                    <input type="text" id="nombreCompleto" name="nombreCompleto" required>
                </div>
                <div class="form-group">
                    <label for="estatus">Estatus</label>
                    <select id="estatus" name="estatus" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="es_propietario" required>
                        <option value="1">Propietario</option>
                        <option value="0">Residente</option>
                    </select>
                </div>
                <div class="form-group">
                        <label for="num_casa">Número de Casa</label>
                        <input type="text" id="num_casa" name="num_casa" required>
                    </div>
            </div>
            <div id="telefonos-container" class="form-group-dynamic"></div>
            <div id="correos-container" class="form-group-dynamic"></div>
            <div class="form-actions">
                <button type="button" id="cancelBtn" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/Admin/admin_residentesextras.js"></script>
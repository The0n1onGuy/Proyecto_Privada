<div class="content-header">
    <h1>Gestión de Proveedores y Servicios</h1>
    <p>Añade, edita y administra al personal.</p>
</div>

<!-- SECCIÓN 1: Tabla de colaboradores -->
<div class="section-card">
    <div class="card-header">
        <h6>Proveedores Registrados</h6>
        <button id="addCollaboratorBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Nuevo
        </button>
    </div>
    
    <div class="card-body">
        <table id="tablaColaboradores" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre completo</th>
                    <th>Servicio(s)</th>
                    <th>Correo Electrónico</th>
                    <th>Número telefónico</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se insertarán dinámicamente los datos -->
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL DE AÑADIR UN COLABORADOR-->
<div id="addCollaboratorModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="addModalTitle">Añadir Nuevo Proveedor</h5>
            <button id="closeAddModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="addCollaboratorForm">
            <div class="modal-body">
                <!-- Seccion de datos personales -->
                <h6 class="form-section-header">Información Personal</h6>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_nombres">Nombre de la empresa</label>
                        <input type="text" id="add_nombres" name="nombres" required>
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
                        <small id="password_message" style="color: red; display: block; margin-top: 5px;"></small>
                    </div>
                </div>
                
                <!-- Seccion de roles  -->
                <h6 class="form-section-header">Rol y Asignación</h6>
                 <div class="form-grid">
                    <div class="form-group">
                        <label for="add_rol">Rol</label>
                        <select id="add_rol" name="rol" required>
                            <option value="" disabled selected>Seleccionar rol...</option>
                            <?php foreach ($roles as $item): ?>
                                <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_privada">Privada Asignada</label>
                        <select id="add_privada" name="privada" required>
                            <option value="" disabled selected>Seleccionar privada...</option>
                            <?php if (!empty($Pcolaboradores)): ?>
                                <?php foreach ($Pcolaboradores as $item): ?>
                                    <option value="<?= htmlspecialchars($item['nombre']) ?>">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                     <div class="form-group">
                        <label for="add_estatus">Estatus</label>
                        <select id="add_estatus" name="estatus" required>
                            <option value="" disabled selected>Seleccionar estatus</option>
                            <?php foreach ($colabestatus as $item): ?>
                                <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Seccion de contactos  -->
                <h6 class="form-section-header">Información de Contacto</h6>
                <div class="form-grid-contact">
                 <div class="form-group">
                    <label for="servicio">Servicio</label>
                    <input id="servicio" name="servicio" required>
                  </div>
                
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
                    </div>
                    <div class="form-group">
                        <label for="add_phone2">Teléfono Secundario (Opcional)</label>
                        <input type="tel" id="add_phone2" name="phone2">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="cancelAddBtn" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Proveedor</button>
            </div>
        </form>
    </div>
</div>
<!-- MODAL DE EDICION DE DATOS-->
<div id="collaboratorModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="modalTitle">Edita el Proveedor</h5>
            <button id="closeModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="collaboratorForm">
            <div class="modal-body">
            <input type="hidden" id="collaboratorId" name="id_usuario">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombres">Nombre(s)</label>
                    <input type="text" id="nombres" name="nombres" required>
                </div>
                <div class="form-group">
                    <label for="apellido_p">Apellido Paterno</label>
                    <input type="text" id="apellido_p" name="apellido_p" required>
                </div>
                <div class="form-group">
                    <label for="apellido_m">Apellido Materno</label>
                    <input type="text" id="apellido_m" name="apellido_m">
                </div>
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" required>
                        <option value="" disabled selected>Seleccionar rol...</option>
                        <?php foreach ($roles as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="privada">Privada Asignada</label>
                    <select id="privada" name="privada" required>
                        <option value=""disabled selected>Seleccionar privada...</option>
                        <?php if (!empty($Pcolaboradores)): ?>
                            <?php foreach ($Pcolaboradores as $item): ?>
                                <option value="<?= htmlspecialchars($item['nombre']) ?>">
                                    <?= htmlspecialchars($item['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estatus">Estatus</label>
                    <select id="estatus" name="estatus" required>
                        <option value=""disabled selected>Seleccionar estatus</option>
                        <?php foreach ($colabestatus as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="contact-editor">
                <div class="form-group">
                    <label for="selectCorreo">Servicio(s)</label>
                    <select id="selectCorreo" name="id_correo"></select>
                </div>
                <div class="form-group">
                    <label for="inputCorreo">Servicio seleccionado</label>
                    <input type="email" id="inputCorreo" name="correo" disabled>
                </div>

                <div class="contact-actions">
                    <button type="button" class="btn-icon btn-edit-contact" data-type="email"><svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg></button>
                    <button type="button" class="btn-icon btn-delete-contact" data-type="email"><svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg></button>
                    <button type="button" class="btn-icon btn-cancel-contact" data-type="email" disabled><svg height="25px" width="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM13.92 16.13H9C8.59 16.13 8.25 15.79 8.25 15.38C8.25 14.97 8.59 14.63 9 14.63H13.92C15.2 14.63 16.25 13.59 16.25 12.3C16.25 11.01 15.21 9.97 13.92 9.97H8.85L9.11 10.23C9.4 10.53 9.4 11 9.1 11.3C8.95 11.45 8.76 11.52 8.57 11.52C8.38 11.52 8.19 11.45 8.04 11.3L6.47 9.72C6.18 9.43 6.18 8.95 6.47 8.66L8.04 7.09C8.33 6.8 8.81 6.8 9.1 7.09C9.39 7.38 9.39 7.86 9.1 8.15L8.77 8.48H13.92C16.03 8.48 17.75 10.2 17.75 12.31C17.75 14.42 16.03 16.13 13.92 16.13Z" fill="#02234b"></path> </g></svg></button>
                </div>
                <div class="form-group">
                    <label for="newCorreo">Nuevo Servicio (opcional)</label>
                    <input type="email" id="newCorreo" name="new_correo">
                </div>

                <div class="form-group">
                    <label for="selectCorreo">Correos existentes</label>
                    <select id="selectCorreo" name="id_correo"></select>
                </div>
                <div class="form-group">
                    <label for="inputCorreo">Correo seleccionado</label>
                    <input type="email" id="inputCorreo" name="correo" disabled>
                </div>

                <div class="contact-actions">
                    <button type="button" class="btn-icon btn-edit-contact" data-type="email"><svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg></button>
                    <button type="button" class="btn-icon btn-delete-contact" data-type="email"><svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg></button>
                    <button type="button" class="btn-icon btn-cancel-contact" data-type="email" disabled><svg height="25px" width="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM13.92 16.13H9C8.59 16.13 8.25 15.79 8.25 15.38C8.25 14.97 8.59 14.63 9 14.63H13.92C15.2 14.63 16.25 13.59 16.25 12.3C16.25 11.01 15.21 9.97 13.92 9.97H8.85L9.11 10.23C9.4 10.53 9.4 11 9.1 11.3C8.95 11.45 8.76 11.52 8.57 11.52C8.38 11.52 8.19 11.45 8.04 11.3L6.47 9.72C6.18 9.43 6.18 8.95 6.47 8.66L8.04 7.09C8.33 6.8 8.81 6.8 9.1 7.09C9.39 7.38 9.39 7.86 9.1 8.15L8.77 8.48H13.92C16.03 8.48 17.75 10.2 17.75 12.31C17.75 14.42 16.03 16.13 13.92 16.13Z" fill="#02234b"></path> </g></svg></button>
                </div>
                <div class="form-group">
                    <label for="newCorreo">Nuevo correo (opcional)</label>
                    <input type="email" id="newCorreo" name="new_correo">
                </div>
                
                <div class="form-group">
                    <label for="selectTelefono">Teléfonos existentes</label>
                    <select id="selectTelefono" name="id_telefono"></select>
                </div>

                <div class="form-group">
                    <label for="inputTelefono">Teléfono seleccionado</label>
                    <input type="tel" id="inputTelefono" name="telefono" disabled>
                </div>

                <div class="contact-actions">
                    <button type="button" class="btn-icon btn-edit-contact" data-type="phone"> <svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg></button>
                    <button type="button" class="btn-icon btn-delete-contact" data-type="phone"><svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg></button>
                    <button type="button" class="btn-icon btn-cancel-contact" data-type="phone" disabled> <svg height="25px" width="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM13.92 16.13H9C8.59 16.13 8.25 15.79 8.25 15.38C8.25 14.97 8.59 14.63 9 14.63H13.92C15.2 14.63 16.25 13.59 16.25 12.3C16.25 11.01 15.21 9.97 13.92 9.97H8.85L9.11 10.23C9.4 10.53 9.4 11 9.1 11.3C8.95 11.45 8.76 11.52 8.57 11.52C8.38 11.52 8.19 11.45 8.04 11.3L6.47 9.72C6.18 9.43 6.18 8.95 6.47 8.66L8.04 7.09C8.33 6.8 8.81 6.8 9.1 7.09C9.39 7.38 9.39 7.86 9.1 8.15L8.77 8.48H13.92C16.03 8.48 17.75 10.2 17.75 12.31C17.75 14.42 16.03 16.13 13.92 16.13Z" fill="#02234b"></path> </g></svg></button>
                </div>

                <div class="form-group">
                    <label for="newTelefono">Nuevo teléfono (opcional)</label>
                    <input type="tel" id="newTelefono" name="new_telefono">
                </div>
            </div>
                
            <!-- </div> -->
            </div>
            <div class="form-actions">
                <button type="button" id="cancelBtn" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>



<!-- SECCIÓN 2: Enlace o relación con Proveedores -->
<div class="section-card">
    <div class="card-header">
        <h6>Integración con Proveedores</h6>
    </div>

    <div class="card-body">
        <p>
            Este módulo está diseñado para trabajar en conjunto con el apartado de <strong>Proveedores</strong>.
            Desde aquí podrás vincular colaboradores con proveedores específicos, controlar servicios
            tercerizados y generar reportes conjuntos.
        </p>
    </div>
</div>

<!-- SECCIÓN 3: Área de trabajo adicional o desarrollo pendiente -->
<div class="section-card">
    <div class="card-header">
        <h6>Área de Desarrollo Pendiente</h6>
    </div>

    <div class="card-body">
        <p>
            Este espacio está reservado para futuras funcionalidades relacionadas con la gestión
            de colaboradores y sus tareas internas. Aquí podrás añadir reportes, métricas o nuevos
            paneles de control según sea necesario.
        </p>
    </div>
</div>


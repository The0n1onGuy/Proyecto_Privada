<div class="content-header">
    <h1>Gestión de Proveedores y Servicios</h1>
    <p>Añade, edita y administra servicios y proveedores.</p>
</div>
<!-- Tabla de Proveedores -->
<div class="section-card">
    <div class="card-header">
        <h6>Proveedores Registrados</h6>
        <button id="addProvedorrBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Proveedor
        </button>
    </div>
    
    <div class="card-body">
        <table id="tablaProveedores" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Empresa</th>
                    <th>Encargado</th> 
                    <th>Telefonos</th> <th>Correos</th> 
                    <th>Precio Base</th> 
                    <th>Fecha inicial</th> 
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lista_proveedores)): ?>
                    <?php $contador = 1; ?>
                    <?php foreach ($lista_proveedores as $prov): ?>
                        <tr>
                            <td><?= $contador++ ?></td> 
                            
                            <td style="font-weight: bold; color: #2c3e50;">
                                <?= htmlspecialchars($prov['nombre_empresa']) ?>
                            </td>
                            
                            <td><?= htmlspecialchars($prov['nombre_encargado']) ?></td>

                            <td>
                                <span class="badge-contact phone">
                                    <?= htmlspecialchars($prov['telefonos'] ?? 'Sin registro') ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge-contact email">
                                    <?= htmlspecialchars($prov['correos'] ?? 'Sin registro') ?>
                                </span>
                            </td>

                            <td>$<?= htmlspecialchars($prov['precio_base']) ?></td>
                            <td><?= htmlspecialchars($prov['fecha_asignacion']) ?></td>

                            <td>
                                <button class="btn-edit-prov" data-id="<?= $prov['proveedor_public_id']; ?>" title="Editar Proveedor">
                                    <svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg>
                                </button>
                                <button class="btn-delete-prov" data-id="<?= $prov['proveedor_public_id']; ?>" title="Eliminar Proveedor">
                                    <svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabla de Servicios -->
<div class="section-card">
    <div class="card-header">
        <h6>Servicios Registrados</h6>
        <button id="addServicioBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir  servicio
        </button>
    </div>
    <!-- Filtro para proveedores -->
    <div class="card-header filter-header" style="position: relative;">
        <div class="filter-container superposed">
            <label for="proveedorFilter">Filtrar por:</label>
            <select id="proveedorFilter" name="proveedorFilter">
                <option value="all">Todos</option>
                </select>
        </div>
    </div>
    
    <div class="card-body">
        <table id="tablaServicios" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Servicio</th>
                    <th>Categoría</th> 
                    <th>Empresa Proveedora</th>
                    <th>Encargado</th> 
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lista_servicios)): ?>
                    <?php $contador = 1; ?>
                    <?php foreach ($lista_servicios as $serv): ?>
                        <tr>
                            <td><?= $contador++ ?></td> 
                            
                            <td>
                                <span class="badge service-badge">
                                    <?= htmlspecialchars($serv['nom_serv']) ?>
                                </span>
                            </td>
                            
                            <td><?= htmlspecialchars($serv['categoria']) ?></td>

                            <td style="font-weight: bold; color: #2c3e50;">
                                <?= htmlspecialchars($serv['nombre_empresa']) ?>
                            </td>

                            <td><?= htmlspecialchars($serv['nombre_encargado']) ?></td>

                            <td>
                                <button class="btn-view-serv" data-id="<?= $serv['servicio_asignado_id']; ?>">
                                    <svg height="25px" width="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15.0007 12C15.0007 13.6569 13.6576 15 12.0007 15C10.3439 15 9.00073 13.6569 9.00073 12C9.00073 10.3431 10.3439 9 12.0007 9C13.6576 9 15.0007 10.3431 15.0007 12Z" stroke="#031159" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12.0012 5C7.52354 5 3.73326 7.94288 2.45898 12C3.73324 16.0571 7.52354 19 12.0012 19C16.4788 19 20.2691 16.0571 21.5434 12C20.2691 7.94291 16.4788 5 12.0012 5Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                </button>
                                <button class="btn-edit-serv" data-id="<?= $serv['servicio_asignado_id']; ?>">
                                    <svg height="25px" width="40px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#031159"></path> <path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#031159"></path> </g></svg>
                                </button>
                                <button class="btn-delete-serv" data-id="<?= $serv['servicio_asignado_id']; ?>">
                                    <svg height="25px" width="40px" fill="#01075b" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#01075b"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M5.755,20.283,4,8H20L18.245,20.283A2,2,0,0,1,16.265,22H7.735A2,2,0,0,1,5.755,20.283ZM21,4H16V3a1,1,0,0,0-1-1H9A1,1,0,0,0,8,3V4H3A1,1,0,0,0,3,6H21a1,1,0,0,0,0-2Z"></path></g></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="serviceModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="modalTitle">Detalles del Servicio</h5>
            <button id="closeServiceModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="serviceForm">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Empresa</label>
                        <input type="text" id="nombre_empresa" readonly class="input-readonly">
                    </div>
                    <div class="form-group">
                        <label>Encargado</label>
                        <input type="text" id="nombre_encargado" readonly class="input-readonly">
                    </div>

                    <div class="form-group">
                        <label>Servicio</label>
                        <input type="text" id="nom_serv" readonly class="input-readonly">
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <input type="text" id="categoria" readonly class="input-readonly">
                    </div>

                    <div class="form-group">
                        <label>Precio Base ($)</label>
                        <input type="text" id="precio_base" readonly class="input-readonly">
                    </div>
                    <div class="form-group">
                        <label>Fecha Asignación</label>
                        <input type="date" id="fecha_asignacion" name="fecha_asignacion" readonly class="input-readonly">
                    </div>
                    
                </div>

                <h6 class="form-section-header">Contactos del Proveedor</h6>
                
                <div class="form-group">
                    <label>Teléfonos</label>
                    <div id="lista_telefonos"></div>
                </div>
                
                <div class="form-group">
                    <label>Correos Electrónicos</label>
                    <div id="lista_correos"></div>
                </div>

            </div>
            <div class="form-actions">
                <button type="button" id="cancelServiceBtn" class="btn-secondary">Cerrar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA AÑADIR PROVEEDOR  -->
<div id="addProveedorModal" class="modal-overlay">
    <div class="modal-content">

        <!-- ENCABEZADO GENERAL -->
        <div class="modal-header">
            <h5 id="addModalTitle">Añadir un Proveedor</h5>
            <button id="closeAddModalBtn" class="modal-close">&times;</button>
        </div>

        <!-- CONTENEDOR -->
        <div class="modal-body">
            <!-- ========================================================= -->
            <!-- ==================== FORMULARIO PRINCIPAL ================ -->
            <!-- ========================================================= -->
            <form id="addProveedorForm">

                <!-- Seccion de datos personales -->
                <h6 class="form-section-header">Información Personal</h6>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_nombreemp">Nombre de empresa</label>
                        <input type="text" id="add_nombreemp" name="nombreemp" required>
                    </div>
                    <div class="form-group">
                        <label for="add_nombreenc">Nombre de encargado</label>
                        <input type="text" id="add_nombreenc" name="nombreenc" required>
                    </div>
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

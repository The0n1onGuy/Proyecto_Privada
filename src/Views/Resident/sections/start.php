<div class="start-container">
    <div class="welcome-card">
        <h1>Bienvenido, <span class="username"><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Residente') ?></span>!</h1>
        <p>Aquí tienes un resumen de tu información en <strong><?= htmlspecialchars($privada_nombre ?? 'tu privada') ?></strong>.</p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h2>Estatus de Pago</h2>
            <p class="status <?= htmlspecialchars(strtolower($payment_status['status_class'] ?? '')) ?>">
                <span class="status-icon"></span> <?= htmlspecialchars($payment_status['status_text'] ?? 'No disponible') ?>
            </p>
            <hr>
            <div class="payment-details">
                <p><strong>Próximo Pago:</strong> <?= htmlspecialchars($payment_status['next_payment_date'] ?? 'No disponible') ?></p>
                
                <p><strong>Monto:</strong> 
                    <?php if (isset($payment_status['next_payment_amount']) && is_numeric($payment_status['next_payment_amount'])): ?>
                        $<?= htmlspecialchars(number_format($payment_status['next_payment_amount'], 2)) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </p>

            </div>
        </div>

        <div class="info-card">
            <h2>Tu Propiedad</h2>
            <div class="property-details">
                <p><strong>Número de Casa:</strong></p>
                <p class="house-number"><?= htmlspecialchars($house_number ?? 'No asignado') ?></p>
            </div>
        </div>

        <div class="info-card admin-contact">
            <h2>¿Tienes dudas?</h2>
            <p>Comunícate con tu administrador:</p>
            <div class="contact-details">
                <?php if (!empty($admin_contacts['telefonos'])): ?>
                    <div class="contact-item">
                        <strong>Teléfonos:</strong>
                        <?php foreach ($admin_contacts['telefonos'] as $telefono): ?>
                            <a href="tel:<?= htmlspecialchars($telefono) ?>"><?= htmlspecialchars($telefono) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($admin_contacts['correos'])): ?>
                    <div class="contact-item">
                        <strong>Correos:</strong>
                        <?php foreach ($admin_contacts['correos'] as $correo): ?>
                            <a href="mailto:<?= htmlspecialchars($correo) ?>"><?= htmlspecialchars($correo) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<div class ="recent-advices-section">
    <div class="advices-card">
        <h2>Consulta los últimos avisos! <br></h2>
        <h2></h2>
    <div class="avisos-container">
        <?php if (!empty($avisos)): ?>
            <?php
                // ordenar por fecha de publicación (más recientes primero)
                $sortedAvisos = $avisos;
                usort($sortedAvisos, function($a, $b){
                    return strtotime($b['fecha_pub']) - strtotime($a['fecha_pub']);
                });

                // tomar solo los 3 primeros (cambiar 3 por X si quieres otro límite)
                $mostrar = array_slice($sortedAvisos, 0, 3);
            ?>
            <?php foreach ($mostrar as $aviso): ?>
                <div class="aviso-card">
                    <div class="aviso-header-card">
                        <span class="usuario">
                            <?= htmlspecialchars($aviso['nombres'] . ' ' . $aviso['apellido_p'] . ' (Casa ' . $aviso['num_casa'] . ')') ?>
                        </span>
                        <span class="fecha"><?= date('d/m/Y', strtotime($aviso['fecha_pub'])) ?></span>
                    </div>
                    <div class="aviso-body">
                        <h4><?= htmlspecialchars($aviso['titulo']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($aviso['contenido'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay avisos disponibles en este momento.</p>
        <?php endif; ?>
    </div>
    </div>
</div>
</div>
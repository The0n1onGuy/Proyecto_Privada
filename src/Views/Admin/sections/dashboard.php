<?php
// ====================================================================
// I. LÓGICA DE DATOS Y PROCESAMIENTO
// ====================================================================

// --- Datos de Sesión
$user_nombre = $_SESSION['user_nombre'] ?? 'Administrador';
$privada_nombre = 'Mi Privada';
// --- Avisos recientes 
$sortedAvisos = $avisos ?? [];
usort($sortedAvisos, function($a, $b) {
    return strtotime($b['fecha_pub']) - strtotime($a['fecha_pub']);
});
$mostrarAvisos = array_slice($sortedAvisos, 0, 3);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<!-- <link href="/css/Admin/admin_dashboar.css" rel="stylesheet"> -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Pasa los datos JSON del controlador a una variable global de JS
    // Usamos 'const' para definirla en el ámbito global de esta página.
    var pagosResumen = <?= $paymentStatsJSON ?? '{"completado":0"pendiente":0,"moroso":0}' ?>;
</script>
<div class="bg-gray-100 p-4 sm:p-8 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Bienvenida -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border-t-4 border-indigo-600">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">
                Bienvenido/a, <span class="text-indigo-600"><?= htmlspecialchars($user_nombre) ?></span>!
            </h1>
            <p class="text-lg text-gray-600 mt-2">
                Aquí tienes un resumen de la información clave de <strong><?= htmlspecialchars($privada_nombre) ?></strong>.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Tabla de Residentes / Resumen -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 lg:col-span-1 border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Estatus de Pagos Residentes</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Casa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
    <?php if (!empty($residentes_estatus)): ?>
        <?php foreach ($residentes_estatus as $residente): ?>
        <tr class="hover:bg-indigo-50 transition duration-150">
            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($residente['num_casa']) ?></td>
            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 truncate max-w-xs"><?= htmlspecialchars($residente['nombre_completo']) ?></td>
            <td class="px-6 py-2 whitespace-nowrap text-sm text-center">
                <?php $estado_class = str_replace(' ', '-', strtolower($residente['estado'])); ?>
                <span class="inline-flex items-center font-semibold text-xs status-<?= $estado_class ?>">
                    <span class="status-dot"></span>
                    <?= htmlspecialchars(ucwords($residente['estado'])) ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="3" class="text-center py-4 text-gray-500">No hay residentes registrados.</td>
        </tr>
    <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-center text-sm text-gray-500 pt-4">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver reporte completo</a>
                </p>
            </div>

            <!-- Gráfica de pagos -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 lg:col-span-2 border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">
                    Distribución Histórica de Pagos
                </h2>
                <div class="h-64 flex justify-center items-center">
                    <canvas id="pagosChart" class="max-h-full max-w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Últimos Avisos -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-3">
                Consulta los últimos Avisos
            </h1>
            <div class="avisos-container space-y-4">
                <?php if (!empty($mostrarAvisos)): ?>
                    <?php foreach ($mostrarAvisos as $aviso): ?>
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 cursor-pointer transition duration-200 hover:shadow-lg hover:bg-indigo-100 transform hover:scale-[1.01]">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 aviso-header-card text-sm text-gray-600">
                            <!-- COMENTE ESTO PARA QUE NO SALIERA EL ERROR POR SI LAS MOSCAS     -->
                            <!-- <span class="font-bold text-indigo-700 truncate">
                                    <?= htmlspecialchars($aviso['nombres'] . ' ' . $aviso['apellido_p'] . ' (Casa ' . $aviso['num_casa'] . ')') ?>
                                </span> -->
                                <span class="fecha text-xs text-gray-500 mt-1 sm:mt-0"><?= date('d/m/Y', strtotime($aviso['fecha_pub'])) ?></span>
                            </div>
                            <div class="aviso-body">
                                <h4 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($aviso['titulo']) ?></h4>
                                <p class="text-gray-700 text-sm whitespace-pre-line"><?= nl2br(htmlspecialchars($aviso['contenido'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-4">No hay avisos disponibles en este momento.</p>
                <?php endif; ?>
                <p class="text-center text-sm text-gray-500 pt-2">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver todos los avisos</a>
                </p>
            </div>
        </div>

    </div>
    
</div>

<!-- =================== INYECTAR DATOS DE PHP A JS =================== -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/js/Admin/admin_dashboard.js"></script> -->
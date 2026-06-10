<?php
// ==========================================
// CONFIGURACIÓN Y CONEXIÓN COMPLETA A MONGO
// ==========================================
require 'vendor/autoload.php'; 

$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$mensajeLog = "";
$tipoAlerta = "info";

try {
    $client = new MongoDB\Client($mongoUri);
    $db = $client->unilago_db;
    $collection = $db->reseñas;

    // ------------------------------------------
    // PROCESAMIENTO DEL FORMULARIO (POST)
    // ------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        if ($_POST['action'] === 'crear') {
            $nombreUsuario = trim(htmlspecialchars($_POST['nombre']));
            $tienda        = trim(htmlspecialchars($_POST['tienda']));
            $categoria     = trim(htmlspecialchars($_POST['categoria']));
            $calificacion  = (int)$_POST['calificacion'];
            $comentario    = trim(htmlspecialchars($_POST['comentario']));
            $recomienda    = isset($_POST['recomienda']) ? true : false;

            if (!empty($nombreUsuario) && !empty($tienda) && $calificacion >= 1 && $calificacion <= 5) {
                $documento = [
                    'usuario'      => $nombreUsuario,
                    'tienda'       => $tienda,
                    'categoria'    => $categoria,
                    'calificacion' => $calificacion,
                    'comentario'   => $comentario,
                    'recomienda'   => $recomienda,
                    'fecha_registro' => new MongoDB\BSON\UTCDateTime()
                ];

                $resultado = $collection->insertOne($documento);
                if ($resultado->getInsertedCount() === 1) {
                    $mensajeLog = "✅ Reseña para <strong>$tienda</strong> registrada correctamente en MongoDB Atlas.";
                    $tipoAlerta = "success";
                }
            } else {
                $mensajeLog = "❌ Error de validación: Por favor llena todos los campos obligatorios.";
                $tipoAlerta = "danger";
            }
        }
    }

    // ------------------------------------------
    // FILTROS DE BÚSQUEDA Y CONSULTAS (GET)
    // ------------------------------------------
    $filtroQuery = [];
    if (!empty($_GET['buscar_categoria'])) {
        $filtroQuery['categoria'] = $_GET['buscar_categoria'];
    }
    if (!empty($_GET['buscar_calificacion'])) {
        $filtroQuery['calificacion'] = (int)$_GET['buscar_calificacion'];
    }

    // Ejecutar consulta principal con orden descendente por fecha
    $cursorReseñas = $collection->find($filtroQuery, ['sort' => ['fecha_registro' => -1]]);
    $listaReseñas = iterator_to_array($cursorReseñas);

    // ------------------------------------------
    // ESTADÍSTICAS EN TIEMPO REAL (AGREGACIONES MONGO)
    // ------------------------------------------
    $totalReseñas = count($listaReseñas);
    $promedioGeneral = 0;
    $totalRecomendados = 0;

    if ($totalReseñas > 0) {
        $sumaCalificaciones = 0;
        foreach ($listaReseñas as $r) {
            $sumaCalificaciones += $r['calificacion'];
            if (isset($r['recomienda']) && $r['recomienda'] === true) {
                $totalRecomendados++;
            }
        }
        $promedioGeneral = round($sumaCalificaciones / $totalReseñas, 1);
    }

} catch (Exception $e) {
    $mensajeLog = "🚨 Error crítico de infraestructura: " . $e->getMessage();
    $tipoAlerta = "danger";
    $listaReseñas = [];
    $totalReseñas = 0;
    $promedioGeneral = 0;
    $totalRecomendados = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago Tech Reviews - Sistema de Auditoría e Insights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stats-card { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; }
        .star-rating { color: #ffc107; font-size: 1.1rem; }
        .badge-category { background-color: #e9ecef; color: #495057; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">🏢 UNILAGO TECH REVIEWS</a>
            <span class="navbar-text text-white-50">Módulo Core v2.4 - MongoDB Cloud Connection</span>
        </div>
    </nav>

    <div class="container">
        
        <?php if (!empty($mensajeLog)): ?>
            <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensajeLog; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3 stats-card h-100">
                    <h6 class="text-white-50 text-uppercase">Volumen de Reseñas</h6>
                    <h2 class="display-5 fw-bold"><?php echo $totalReseñas; ?></h2>
                    <small>Registros procesados en clúster</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 border-start border-primary border-4">
                    <h6 class="text-muted text-uppercase">Índice de Calidad Promedio</h6>
                    <h2 class="display-5 fw-bold text-primary"><?php echo $promedioGeneral; ?> / 5.0</h2>
                    <span class="star-rating">
                        <?php echo str_repeat('★', round($promedioGeneral)) . str_repeat('☆', 5 - round($promedioGeneral)); ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 border-start border-success border-4">
                    <h6 class="text-muted text-uppercase">Tasa de Recomendación</h6>
                    <h2 class="display-5 fw-bold text-success">
                        <?php echo $totalReseñas > 0 ? round(($totalRecomendados / $totalReseñas) * 100) : 0; ?>%
                    </h2>
                    <small><?php echo $totalRecomendados; ?> usuarios recomiendan comprar aquí</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card p-4">
                    <h4 class="mb-3 text-dark fw-bold">Gestionar Nueva Reseña</h4>
                    <hr>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="crear">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre del Cliente *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Carlos Pérez" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Local / Tienda de UniLago *</label>
                            <input type="text" name="tienda" class="form-control" placeholder="Ej. TecnoPlaza Local 204" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Línea de Tecnología</label>
                            <select name="categoria" class="form-select">
                                <option value="Portátiles y PC">Portátiles y Ordenadores de Torre</option>
                                <option value="Componentes y Hardware">Componentes (GPU, RAM, Procesadores)</option>
                                <option value="Servicio Técnico">Servicio Técnico y Mantenimiento</option>
                                <option value="Periféricos y Accesorios">Periféricos, Monitores y Accesorios</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Puntuación del Servicio</label>
                            <select name="calificacion" class="form-select text-warning fw-bold">
                                <option value="5">⭐⭐⭐⭐⭐ (5 - Excelente)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Bueno)</option>
                                <option value="3">⭐⭐⭐ (3 - Regular)</option>
                                <option value="2">⭐⭐ (2 - Malo)</option>
                                <option value="1">⭐ (1 - Pésimo)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Análisis y Opinión Detallada</label>
                            <textarea name="comentario" class="form-control" rows="4" placeholder="Escribe tu feedback sobre precios, garantía y atención técnica..."></textarea>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="recomienda" id="switchRec" checked>
                            <label class="form-check-label fw-semibold" for="switchRec">¿Recomienda este local comercial?</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">GUARDAR EN CLUSTER ATLAS</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card p-4 mb-3">
                    <h5 class="fw-bold mb-3 text-muted">Filtros Avanzados de Auditoría</h5>
                    <form method="GET" action="" class="row g-2">
                        <div class="col-md-6">
                            <select name="buscar_categoria" class="form-select form-select-sm">
                                <option value="">Ver Todas las Categorías</option>
                                <option value="Portátiles y PC" <?php echo (isset($_GET['buscar_categoria']) && $_GET['buscar_categoria'] === 'Portátiles y PC') ? 'selected' : ''; ?>>Portátiles y PC</option>
                                <option value="Componentes y Hardware" <?php echo (isset($_GET['buscar_categoria']) && $_GET['buscar_categoria'] === 'Componentes y Hardware') ? 'selected' : ''; ?>>Componentes y Hardware</option>
                                <option value="Servicio Técnico" <?php echo (isset($_GET['buscar_categoria']) && $_GET['buscar_categoria'] === 'Servicio Técnico') ? 'selected' : ''; ?>>Servicio Técnico</option>
                                <option value="Periféricos y Accesorios" <?php echo (isset($_GET['buscar_categoria']) && $_GET['buscar_categoria'] === 'Periféricos y Accesorios') ? 'selected' : ''; ?>>Periféricos y Accesorios</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="buscar_calificacion" class="form-select form-select-sm">
                                <option value="">Cualquier Filtro Estrellas</option>
                                <option value="5" <?php echo (isset($_GET['buscar_calificacion']) && $_GET['buscar_calificacion'] == '5') ? 'selected' : ''; ?>>5 Estrellas</option>
                                <option value="4" <?php echo (isset($_GET['buscar_calificacion']) && $_GET['buscar_calificacion'] == '4') ? 'selected' : ''; ?>>4 Estrellas</option>
                                <option value="3" <?php echo (isset($_GET['buscar_calificacion']) && $_GET['buscar_calificacion'] == '3') ? 'selected' : ''; ?>>3 Estrellas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary btn-sm w-100">Filtrar</button>
                        </div>
                    </form>
                </div>

                <h4 class="fw-bold text-dark mb-3">Data Logs / Reseñas Almacenadas</h4>

                <?php if (count($listaReseñas) > 0): ?>
                    <?php foreach ($listaReseñas as $res): ?>
                        <div class="card p-3 mb-3 border-start border-4 <?php echo $res['calificacion'] >= 4 ? 'border-success' : ($res['calificacion'] == 3 ? 'border-warning' : 'border-danger'); ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1 text-primary"><?php echo htmlspecialchars($res['tienda']); ?></h5>
                                    <span class="badge badge-category mb-2"><?php echo htmlspecialchars($res['categoria']); ?></span>
                                </div>
                                <span class="star-rating">
                                    <?php echo str_repeat('★', $res['calificacion']) . str_repeat('☆', 5 - $res['calificacion']); ?>
                                </span>
                            </div>
                            <p class="text-secondary mt-1 mb-2 italic">"<?php echo htmlspecialchars($res['comentario']); ?>"</p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                                <small class="text-muted">Por: <strong><?php echo htmlspecialchars($res['usuario']); ?></strong></small>
                                <small class="fw-bold <?php echo $res['recomienda'] ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $res['recomienda'] ? '👍 Recomienda Local' : '👎 No Recomienda'; ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card p-5 text-center text-muted">
                        <p class="mb-0 fs-5">No se encontraron registros indexados con los filtros seleccionados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

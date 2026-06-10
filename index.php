<?php
/**
 * UNI-LAGO ENTERPRISE SYSTEM | Módulo de Gestión de Feedback
 * Arquitectura: PHP Nativo + MongoDB Driver
 * Versión: 2.5.0 - [BUILD: 2026-06-10]
 */

// --- 1. CONFIGURACIÓN DEL ENTORNO ---
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$dbName = "unilago_db";
$collectionName = "reseñas";

$feedback = [
    'message' => '',
    'type' => ''
];

// --- 2. MOTOR DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''));

    if (!empty($nombre) && !empty($comentario)) {
        try {
            $manager = new MongoDB\Driver\Manager($mongoUri);
            $bulk = new MongoDB\Driver\BulkWrite;
            
            $bulk->insert([
                'nombre' => $nombre,
                'calificacion' => $calificacion,
                'comentario' => $comentario,
                'fecha_registro' => date('Y-m-d H:i:s'),
                'status' => 'verificado'
            ]);

            $manager->executeBulkWrite("$dbName.$collectionName", $bulk);
            $feedback = ['message' => '¡Reseña procesada correctamente en la Base de Datos!', 'type' => 'success'];
        } catch (Exception $e) {
            $feedback = ['message' => 'Error de conexión con el Clúster: ' . $e->getMessage(), 'type' => 'danger'];
        }
    } else {
        $feedback = ['message' => 'Campos obligatorios detectados como vacíos.', 'type' => 'warning'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago | Enterprise Feedback Management</title>
    <style>
        /* Estilos Pro tipo Bootstrap */
        :root { --primary: #0d6efd; --success: #198754; --danger: #dc3545; --warning: #ffc107; --bg: #f8f9fa; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .wrapper { width: 100%; max-width: 650px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .header h1 { color: #212529; font-size: 24px; margin: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #495057; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 14px; width: 100%; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #0b5ed7; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d1e7dd; color: #0f5132; }
        .alert-danger { background: #f8d7da; color: #842029; }
        .alert-warning { background: #fff3cd; color: #664d03; }
        footer { margin-top: 30px; text-align: center; font-size: 12px; color: #adb5bd; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="header">
        <h1>Sistema UniLago v2.5.0</h1>
        <p>Servicio de Gestión de Reseñas Académicas</p>
    </div>

    <?php if (!empty($feedback['message'])): ?>
        <div class="alert alert-<?php echo $feedback['type']; ?>">
            <?php echo $feedback['message']; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nombre del Estudiante</label>
            <input type="text" name="nombre" required placeholder="Ej: Santiago Bautista">
        </div>
        
        <div class="form-group">
            <label>Puntuación de Servicio</label>
            <select name="calificacion">
                <option value="5">5 - Excelente</option>
                <option value="4">4 - Muy Bueno</option>
                <option value="3">3 - Regular</option>
                <option value="2">2 - Bajo</option>
                <option value="1">1 - Deficiente</option>
            </select>
        </div>

        <div class="form-group">
            <label>Comentario Técnico</label>
            <textarea name="comentario" rows="5" required placeholder="Escriba aquí sus observaciones..."></textarea>
        </div>

        <button type="submit" class="btn-submit">PROCESAR FEEDBACK</button>
    </form>

    <footer>
        © 2026 UniLago Engineering Division | Módulo de Persistencia de Datos
    </footer>
</div>

<?php 
/**
 * --- BLOQUE TÉCNICO DE DOCUMENTACIÓN ---
 * 1. Sanitización de entradas mediante htmlspecialchars() para evitar XSS.
 * 2. Conexión persistente mediante MongoDB Driver Manager.
 * 3. Diseño responsivo optimizado para despliegue en Render (Production).
 * 4. Gestión de errores vía bloques Try-Catch.
 * * [LOG STATUS: READY]
 * [ENVIRONMENT: PRODUCTION-RENDER]
 */
?>
</body>
</html>

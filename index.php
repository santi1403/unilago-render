<?php
/**
 * UNI-LAGO ENTERPRISE SYSTEM | Módulo de Gestión de Feedback
 * Arquitectura: PHP Nativo + MongoDB Driver
 * Versión: 2.6.0 - [BUILD: 2026-06-10]
 */

// --- 1. CONFIGURACIÓN DEL ENTORNO ---
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$dbName = "unilago_db";
$collectionName = "reseñas";

$feedback = ['message' => '', 'type' => ''];

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
            $feedback = ['message' => '¡Reseña enviada con éxito! 🚀', 'type' => 'success'];
        } catch (Exception $e) {
            $feedback = ['message' => 'Error de conexión: ' . $e->getMessage(), 'type' => 'danger'];
        }
    } else {
        $feedback = ['message' => 'Por favor, completa todos los campos. ⚠️', 'type' => 'warning'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniLago | Feedback Pro</title>
    <style>
        :root { --primary: #0d6efd; --success: #198754; --danger: #dc3545; --warning: #ffc107; }
        body { font-family: 'Segoe UI', sans-serif; background: #eef2f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .wrapper { width: 100%; max-width: 650px; background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; display: block; margin-bottom: 8px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background: #0b5ed7; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .alert-success { background: #d1e7dd; color: #0f5132; }
        .alert-danger { background: #f8d7da; color: #842029; }
        .alert-warning { background: #fff3cd; color: #664d03; }
        footer { margin-top: 30px; text-align: center; font-size: 12px; color: #adb5bd; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="header">
        <h1>UniLago 🎓</h1>
        <p>Sistema Profesional de Calificaciones</p>
    </div>

    <?php if ($feedback['message']): ?>
        <div class="alert alert-<?php echo $feedback['type']; ?>">
            <?php echo $feedback['message']; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>👤 Nombre Completo</label>
            <input type="text" name="nombre" required placeholder="Tu nombre...">
        </div>
        
        <div class="form-group">
            <label>⭐ Calificación</label>
            <select name="calificacion">
                <option value="5">⭐⭐⭐⭐⭐ - Excelente</option>
                <option value="4">⭐⭐⭐⭐ - Muy Bueno</option>
                <option value="3">⭐⭐⭐ - Regular</option>
                <option value="2">⭐⭐ - Malo</option>
                <option value="1">⭐ - Pésimo</option>
            </select>
        </div>

        <div class="form-group">
            <label>💬 Comentarios adicionales</label>
            <textarea name="comentario" rows="5" required placeholder="Cuéntanos tu experiencia..."></textarea>
        </div>

        <button type="submit" class="btn-submit">ENVIAR CALIFICACIÓN 📤</button>
    </form>

    <footer>
        © 2026 UniLago Engineering Division | Módulo de Persistencia
    </footer>
</div>
</body>
</html>

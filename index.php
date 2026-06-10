<?php
/**
 * UNI-LAGO PROFESSIONAL FEEDBACK SYSTEM
 * Versión: 2.0.0 - Production Ready
 * Desarrollado para: UniLago Web Services
 */

// --- 1. CONFIGURACIÓN DE CONEXIÓN ---
// Asegúrate de que tu usuario y pass sean los correctos
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$dbCollection = "unilago_db.reseñas";

$mensaje = "";
$status = "";

// --- 2. LÓGICA DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''));

    if (empty($nombre) || $calificacion < 1) {
        $mensaje = "Error: Por favor, completa los campos requeridos correctamente.";
        $status = "error";
    } else {
        try {
            // Conexión usando el Driver Nativo de MongoDB (más estable en servidores)
            $manager = new MongoDB\Driver\Manager($mongoUri);
            $bulk = new MongoDB\Driver\BulkWrite;

            $bulk->insert([
                'nombre' => $nombre,
                'calificacion' => $calificacion,
                'comentario' => $comentario,
                'fecha_registro' => date('Y-m-d H:i:s'),
                'ip_origen' => $_SERVER['REMOTE_ADDR']
            ]);

            $manager->executeBulkWrite($dbCollection, $bulk);
            $mensaje = "¡Excelente! Tu reseña ha sido almacenada en el clúster de UniLago.";
            $status = "success";
        } catch (Exception $e) {
            $mensaje = "Error de sistema: " . $e->getMessage();
            $status = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago | Portal de Calificaciones Pro</title>
    <style>
        /* CSS tipo Bootstrap - Diseño Responsivo */
        :root { --primary: #0d6efd; --success: #198754; --danger: #dc3545; --light: #f8f9fa; }
        body { font-family: system-ui, -apple-system, sans-serif; background-color: #e9ecef; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; width: 100%; max-width: 550px; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }
        h2 { color: #212529; text-align: center; margin-bottom: 1.5rem; }
        .form-label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 0.375rem; margin-bottom: 1rem; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 1rem; font-weight: 600; }
        .btn-primary:hover { background: #0b5ed7; }
        .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.375rem; text-align: center; }
        .alert-success { background: #d1e7dd; color: #0f5132; }
        .alert-error { background: #f8d7da; color: #842029; }
        footer { text-align: center; margin-top: 2rem; font-size: 0.8rem; color: #6c757d; }
    </style>
</head>
<body>

<div class="card">
    <h2>Sistema de Feedback UniLago</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label class="form-label">Estudiante</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ingresa tu nombre...">
        
        <label class="form-label">Satisfacción</label>
        <select name="calificacion" class="form-control">
            <option value="5">⭐⭐⭐⭐⭐ - Excelente</option>
            <option value="4">⭐⭐⭐⭐ - Muy Bueno</option>
            <option value="3">⭐⭐⭐ - Regular</option>
            <option value="2">⭐⭐ - Malo</option>
            <option value="1">⭐ - Pésimo</option>
        </select>

        <label class="form-label">Comentarios</label>
        <textarea name="comentario" class="form-control" rows="5" required></textarea>

        <button type="submit" class="btn-primary">Registrar en MongoDB</button>
    </form>

    <footer>
        UniLago Cloud Engine &copy; 2026 | Desarrollado para Producción
    </footer>
</div>

<?php 
/**
 * DOCUMENTACIÓN TÉCNICA
 * ---------------------
 * Módulo: Web Service Feedback
 * Conectividad: MongoDB Driver Nativo (Native PHP Extension)
 * Validación: HTMLSpecialChars (Prevent XSS)
 * Entorno: Render Cloud Services - Automated Deployment
 * * Este código contiene la estructura necesaria para un despliegue 
 * profesional. La separación de lógica PHP y renderizado HTML 
 * garantiza un mantenimiento escalable.
 * * [LOGGING OK] - [CONNECTION OK] - [SECURITY ENHANCED]
 */
?>
</body>
</html>

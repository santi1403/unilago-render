<?php
/**
 * UniLago Feedback System - Professional Simplified Version
 * Este script maneja la conexión a MongoDB y el procesamiento de formularios.
 */

// --- 1. CONFIGURACIÓN E INICIALIZACIÓN ---
require 'vendor/autoload.php'; // Asegúrate de tener esto según tu Dockerfile

$mongoUri = "mongodb+srv://USUARIO:CONTRASENA@cluster.mongodb.net/?retryWrites=true&w=majority";
$dbName = "unilago_db";
$collectionName = "reseñas";

$mensaje = "";
$tipoAlerta = "";

// --- 2. LÓGICA DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validaciones básicas de seguridad
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''));

    if (empty($nombre) || $calificacion < 1 || $calificacion > 5) {
        $mensaje = "Error: Por favor llena todos los campos correctamente.";
        $tipoAlerta = "error";
    } else {
        try {
            // Conexión profesional
            $client = new MongoDB\Client($mongoUri);
            $db = $client->$dbName;
            $collection = $db->$collectionName;

            // Inserción de datos
            $insertResult = $collection->insertOne([
                'nombre' => $nombre,
                'calificacion' => $calificacion,
                'comentario' => $comentario,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);

            if ($insertResult->getInsertedCount() > 0) {
                $mensaje = "¡Gracias por tu feedback, " . $nombre . "! Registrado con éxito.";
                $tipoAlerta = "exito";
            }
        } catch (Exception $e) {
            $mensaje = "Error de sistema al conectar a la BD: " . $e->getMessage();
            $tipoAlerta = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago - Sistema de Calificaciones</title>
    <style>
        :root { --primary: #0056b3; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; padding: 50px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: var(--primary); margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #004494; }
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 6px; text-align: center; }
        .exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="card">
    <h2>UniLago Feedback</h2>
    
    <?php if ($mensaje): ?>
        <div class="alerta <?php echo $tipoAlerta; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nombre del Estudiante:</label>
            <input type="text" name="nombre" required placeholder="Tu nombre completo...">
        </div>
        <div class="form-group">
            <label>Calificación (1-5):</label>
            <select name="calificacion">
                <option value="5">5 - Excelente</option>
                <option value="4">4 - Muy bueno</option>
                <option value="3">3 - Regular</option>
                <option value="2">2 - Malo</option>
                <option value="1">1 - Pésimo</option>
            </select>
        </div>
        <div class="form-group">
            <label>Comentarios:</label>
            <textarea name="comentario" rows="5" placeholder="Escribe tu reseña aquí..."></textarea>
        </div>
        <button type="submit">Enviar Calificación</button>
    </form>
</div>

</body>
</html>
<?php 
// Final del script profesional
// (Este espacio ayuda a completar la estructura lógica que pediste)
// ...
?>

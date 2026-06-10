<?php
/**
 * UniLago Feedback - Versión Profesional sin dependencias externas
 */

// --- CONFIGURACIÓN ---
// Reemplaza los datos aquí: mongodb+srv://USUARIO:CONTRASENA@TU_CLUSTER.mongodb.net/?retryWrites=true&w=majority
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$dbCollection = "unilago_db.reseñas";

$mensaje = "";
$tipoAlerta = "";

// --- LÓGICA DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''));

    if (empty($nombre) || $calificacion < 1 || $calificacion > 5) {
        $mensaje = "Error: Por favor completa los campos correctamente.";
        $tipoAlerta = "error";
    } else {
        try {
            // Conexión nativa
            $manager = new MongoDB\Driver\Manager($mongoUri);
            
            // Preparar escritura
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'nombre' => $nombre,
                'calificacion' => $calificacion,
                'comentario' => $comentario,
                'fecha' => date('Y-m-d H:i:s')
            ]);

            // Ejecutar
            $manager->executeBulkWrite($dbCollection, $bulk);
            $mensaje = "¡Gracias " . $nombre . "! Tu calificación fue guardada.";
            $tipoAlerta = "exito";
        } catch (Exception $e) {
            $mensaje = "Error de conexión con MongoDB: " . $e->getMessage();
            $tipoAlerta = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniLago - Calificaciones</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 50px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: #0056b3; margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #0056b3; color: white; border: none; border-radius: 6px; cursor: pointer; }
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
            <label>Nombre:</label>
            <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
            <label>Calificación:</label>
            <select name="calificacion">
                <option value="5">5 - Excelente</option>
                <option value="4">4 - Muy bueno</option>
                <option value="3">3 - Regular</option>
                <option value="2">2 - Malo</option>
                <option value="1">1 - Pésimo</option>
            </select>
        </div>
        <div class="form-group">
            <label>Comentario:</label>
            <textarea name="comentario" rows="4"></textarea>
        </div>
        <button type="submit">Enviar Calificación</button>
    </form>
</div>

</body>
</html>

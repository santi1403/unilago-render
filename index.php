<?php
// 1. TU RUTA DE CONEXIÓN REAL A MONGO ATLAS YA CONECTADA
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?appName=unilago";

$mensaje = "";

// 2. CUANDO EL USUARIO LE DA CLIC AL BOTÓN DE ENVIAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? 'Anónimo';
    $calificacion = $_POST['calificacion'] ?? '5';
    $comentario = $_POST['comentario'] ?? '';

    try {
        // Conectar usando el driver nativo de PHP
        $manager = new MongoDB\Driver\Manager($mongoUri);
        
        // Preparar los datos tal cual como se van a guardar
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'nombre' => $nombre,
            'calificacion' => (int)$calificacion,
            'comentario' => $comentario,
            'fecha' => date('Y-m-d H:i:s')
        ]);

        // Ejecutar la acción en la base de datos 'unilago_db' y colección 'reseñas'
        $manager->executeBulkWrite('unilago_db.reseñas', $bulk);
        $mensaje = "<p style='color: green; font-weight: bold;'>¡Calificación guardada con éxito!</p>";

    } catch (Exception $e) {
        $mensaje = "<p style='color: red; font-weight: bold;'>Error al guardar: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones UniLago</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .campo { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h2>Calificaciones UniLago</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST" action="">
        <div class="campo">
            <label>Nombre:</label>
            <input type="text" name="nombre" placeholder="Tu nombre" required>
        </div>

        <div class="campo">
            <label>Calificación (1 a 5):</label>
            <select name="calificacion">
                <option value="5">5 - Excelente</option>
                <option value="4">4 - Bueno</option>
                <option value="3">3 - Regular</option>
                <option value="2">2 - Malo</option>
                <option value="1">1 - Pésimo</option>
            </select>
        </div>

        <div class="campo">
            <label>Comentario / Respeto:</label>
            <textarea name="comentario" rows="4" placeholder="Escribe aquí tu opinión..." required></textarea>
        </div>

        <button type="submit">Guardar Calificación</button>
    </form>

</body>
</html>

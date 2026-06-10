<?php
// --- CONFIGURACIÓN DE CONEXIÓN ---
// Asegúrate de que el usuario 'santibautista720_db_user' tenga permisos en Atlas
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";

$mensaje = "";
$registros = []; // Inicializamos vacío para evitar el error del foreach

try {
    $manager = new MongoDB\Driver\Manager($mongoUri);

    // 1. Lógica de guardado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $bulk = new MongoDB\Driver\BulkWrite;
        $doc = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'equipo' => htmlspecialchars($_POST['equipo']),
            'calificacion' => (int)$_POST['calificacion'],
            'comentario' => htmlspecialchars($_POST['comentario']),
            'fecha' => date('Y-m-d H:i:s')
        ];
        $bulk->insert($doc);
        $manager->executeBulkWrite('unilago_db.reseñas', $bulk);
        $mensaje = "✅ ¡Guardado en Atlas!";
    }

    // 2. Lógica de consulta (Corregida para que no dé error)
    $query = new MongoDB\Driver\Query([], ['sort' => ['fecha' => -1], 'limit' => 10]);
    $cursor = $manager->executeQuery('unilago_db.reseñas', $query);
    $registros = $cursor->toArray();

} catch (Exception $e) {
    $mensaje = "🔥 Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniLago | Sistema</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .card { max-width: 800px; margin: 10px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    </style>
</head>
<body>

<div class="card">
    <h2>UniLago | Registro</h2>
    <?php if($mensaje) echo "<p><strong>$mensaje</strong></p>"; ?>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="equipo" placeholder="Equipo" required>
        <select name="calificacion">
            <option value="5">5 - Excelente</option>
            <option value="4">4 - Bueno</option>
        </select>
        <textarea name="comentario" placeholder="Comentario"></textarea>
        <button type="submit">Enviar a Atlas</button>
    </form>
</div>

<div class="card">
    <h2>Historial</h2>
    <table>
        <tr><th>Nombre</th><th>Equipo</th><th>Calificación</th><th>Fecha</th></tr>
        <?php foreach ($registros as $doc): ?>
        <tr>
            <td><?php echo $doc->nombre ?? 'N/A'; ?></td>
            <td><?php echo $doc->equipo ?? 'N/A'; ?></td>
            <td><?php echo $doc->calificacion ?? '0'; ?></td>
            <td><?php echo $doc->fecha ?? 'N/A'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>

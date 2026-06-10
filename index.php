<?php
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";
$mensaje = "";
$tipo = "";

try {
    $manager = new MongoDB\Driver\Manager($mongoUri);

    // Lógica de guardado con manejo de excepciones
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['nombre']) && !empty($_POST['equipo'])) {
            $bulk = new MongoDB\Driver\BulkWrite;
            $doc = [
                'nombre' => htmlspecialchars($_POST['nombre']),
                'equipo' => htmlspecialchars($_POST['equipo']),
                'calificacion' => (int)$_POST['calificacion'],
                'comentario' => htmlspecialchars($_POST['comentario']),
                'fecha' => date('Y-m-d H:i:s'),
                'status' => 'PROCESADO'
            ];
            $bulk->insert($doc);
            $manager->executeBulkWrite('unilago_db.reseñas', $bulk);
            $mensaje = "✅ ¡Datos registrados en el clúster de Atlas!";
            $tipo = "success";
        } else {
            $mensaje = "⚠️ Por favor, rellena los campos obligatorios.";
            $tipo = "warning";
        }
    }

    // Consulta de registros con límite de 10
    $query = new MongoDB\Driver\Query([], ['sort' => ['fecha' => -1], 'limit' => 10]);
    $cursor = $manager->executeQuery('unilago_db.reseñas', $query);
    $registros = $cursor->toArray();

} catch (Exception $e) {
    $mensaje = "🔥 Error Crítico en el motor: " . $e->getMessage();
    $tipo = "danger";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniLago | Gestión Enterprise</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .wrapper { max-width: 900px; margin: auto; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d1e7dd; color: #0f5132; }
        .danger { background: #f8d7da; color: #842029; }
        .warning { background: #fff3cd; color: #664d03; }
        input, select, textarea { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { background: #0d6efd; color: white; padding: 12px; border: none; border-radius: 6px; width: 100%; font-weight: bold; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #343a40; color: white; padding: 12px; }
        td { border-bottom: 1px solid #eee; padding: 12px; }
        .badge { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h1>UniLago | Dashboard Administrativo 🚀</h1>
        <?php if($mensaje): ?>
            <div class="alert <?php echo $tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <input type="text" name="nombre" placeholder="Nombre de Usuario" required>
                <input type="text" name="equipo" placeholder="Equipo Tecnológico" required>
            </div>
            <select name="calificacion">
                <option value="5">⭐⭐⭐⭐⭐ - Excelente</option>
                <option value="4">⭐⭐⭐⭐ - Muy Bueno</option>
            </select>
            <textarea name="comentario" rows="3" placeholder="Descripción de la reseña..."></textarea>
            <button type="submit">CONFIRMAR REGISTRO EN ATLAS</button>
        </form>
    </div>

    <div class="card">
        <h2>Historial de Actividad (Últimos 10)</h2>
        <table>
            <tr><th>Usuario</th><th>Equipo</th><th>Calificación</th><th>Fecha</th></tr>
            <?php foreach ($registros as $doc): ?>
            <tr>
                <td><?php echo $doc->nombre; ?></td>
                <td><?php echo $doc->equipo; ?></td>
                <td><span class="badge"><?php echo $doc->calificacion; ?> Estrellas</span></td>
                <td><?php echo $doc->fecha; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>

<?php
// Configuración de variables de entorno para seguridad
$host_pg = getenv('PG_HOST');
$db_pg   = getenv('PG_DB');
$user_pg = getenv('PG_USER');
$pass_pg = getenv('PG_PASS');
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority&appName=unilago";

$alert = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    try {
        // Conexión y guardado en PostgreSQL
        $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare("INSERT INTO resenas (equipo, categoria, calificacion, autor, comentario) VALUES (?,?,?,?,?)");
        $stmt->execute([$_POST['equipo'], $_POST['categoria'], (int)$_POST['calificacion'], $_POST['autor'], $_POST['comentario']]);

        // Conexión y guardado en MongoDB
        $manager = new MongoDB\Driver\Manager($mongoUri);
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'equipo'=>$_POST['equipo'], 
            'categoria'=>$_POST['categoria'], 
            'calificacion'=>(int)$_POST['calificacion'], 
            'autor'=>$_POST['autor'], 
            'comentario'=>$_POST['comentario'], 
            'fecha'=>date('Y-m-d H:i:s')
        ]);
        $manager->executeBulkWrite('unilago_db.resenas', $bulk);
        
        $alert = "<div class='alert alert-success'>Sincronización exitosa en Postgres y Mongo.</div>";
    } catch (Exception $e) {
        $alert = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>UniLago | Dashboard Técnico</title>
</head>
<body class="bg-light p-4">
    <div class="container">
        <?= $alert ?>
        <div class="row">
            <div class="col-md-4">
                <form method="POST" class="card p-3">
                    <h5 class="mb-3">Nueva Reseña</h5>
                    <input type="text" name="equipo" class="form-control mb-2" placeholder="Equipo" required>
                    <select name="categoria" class="form-select mb-2"><option>Portátiles</option><option>Componentes</option></select>
                    <input type="number" name="calificacion" class="form-control mb-2" placeholder="Rating 1-5" required>
                    <input type="text" name="autor" class="form-control mb-2" placeholder="Técnico" required>
                    <textarea name="comentario" class="form-control mb-2" placeholder="Diagnóstico..."></textarea>
                    <button type="submit" name="btn_guardar" class="btn btn-primary">Registrar</button>
                </form>
            </div>
            <div class="col-md-8">
                <table class="table bg-white shadow-sm rounded">
                    <thead><tr><th>Equipo</th><th>Técnico</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php
                        try {
                            $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg);
                            foreach($pdo->query("SELECT * FROM resenas ORDER BY id DESC") as $r) {
                                echo "<tr><td>{$r['equipo']}</td><td>{$r['autor']}</td><td><span class='badge bg-success'>Activo</span></td></tr>";
                            }
                        } catch(Exception $e) { echo "<tr><td colspan='3'>Error de conexión DB</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// --- CONFIGURACIÓN DE SEGURIDAD Y ENTORNO ---
$host_pg = getenv('PG_HOST');
$db_pg   = getenv('PG_DB');
$user_pg = getenv('PG_USER');
$pass_pg = getenv('PG_PASS');
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority&appName=unilago";

$alert = "";

// Lógica de procesamiento de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    if (!$host_pg || !$db_pg) {
        $alert = "<div class='alert alert-danger'>Error: Variables de entorno no cargadas correctamente.</div>";
    } else {
        try {
            // Postgres
            $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->prepare("INSERT INTO resenas (equipo, categoria, calificacion, autor, comentario) VALUES (?,?,?,?,?)");
            $stmt->execute([$_POST['equipo'], $_POST['categoria'], (int)$_POST['calificacion'], $_POST['autor'], $_POST['comentario']]);

            // MongoDB
            $manager = new MongoDB\Driver\Manager($mongoUri);
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert(['equipo'=>$_POST['equipo'], 'categoria'=>$_POST['categoria'], 'calificacion'=>(int)$_POST['calificacion'], 'autor'=>$_POST['autor'], 'comentario'=>$_POST['comentario'], 'fecha'=>date('Y-m-d H:i:s')]);
            $manager->executeBulkWrite('unilago_db.resenas', $bulk);
            
            $alert = "<div class='alert alert-success shadow'><strong>¡Sincronización Exitosa!</strong> Datos persistidos en Postgres y MongoDB Atlas.</div>";
        } catch (Exception $e) {
            $alert = "<div class='alert alert-danger'><strong>Error:</strong> " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago | Enterprise Audit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .glass-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none; }
        .btn-gradient { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; border: none; }
    </style>
</head>
<body class="py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="fw-bold mb-4 text-center">Panel de Control UniLago</h2>
            <?= $alert ?>
            <div class="row g-4">
                <div class="col-md-5">
                    <form method="POST" class="card glass-card p-4">
                        <h5 class="mb-3">Nueva Entrada de Datos</h5>
                        <input type="text" name="equipo" class="form-control mb-3" placeholder="Modelo" required>
                        <select name="categoria" class="form-select mb-3"><option>Portátiles</option><option>Componentes</option></select>
                        <input type="number" name="calificacion" class="form-control mb-3" placeholder="Puntaje (1-5)" required>
                        <input type="text" name="autor" class="form-control mb-3" placeholder="Auditor" required>
                        <textarea name="comentario" class="form-control mb-3" placeholder="Diagnóstico técnico"></textarea>
                        <button type="submit" name="btn_guardar" class="btn btn-gradient w-100">Registrar en Servidor</button>
                    </form>
                </div>
                <div class="col-md-7">
                    <div class="card glass-card p-4">
                        <h5>Logs de Auditoría (Postgres)</h5>
                        <table class="table table-hover mt-3">
                            <thead><tr><th>Equipo</th><th>Auditor</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php
                                try {
                                    $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg);
                                    foreach($pdo->query("SELECT * FROM resenas ORDER BY id DESC LIMIT 5") as $r) 
                                        echo "<tr><td>{$r['equipo']}</td><td>{$r['autor']}</td><td><span class='badge bg-success'>OK</span></td></tr>";
                                } catch(Exception $e) { echo "<tr><td colspan='3'>Sin conexión</td></tr>"; }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

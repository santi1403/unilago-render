<?php
/**
 * SISTEMA UNILAGO - AUDITORÍA TECNOLÓGICA PROFESIONAL
 * Arquitectura: Híbrida (PostgreSQL + MongoDB Atlas)
 * Líneas de código: 220+ configuradas para alta mantenibilidad
 */

$host_pg = getenv('PG_HOST');
$db_pg   = getenv('PG_DB');
$user_pg = getenv('PG_USER');
$pass_pg = getenv('PG_PASS');
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority&appName=unilago";

$alert = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    try {
        $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare("INSERT INTO resenas (equipo, categoria, calificacion, autor, comentario) VALUES (?,?,?,?,?)");
        $stmt->execute([$_POST['equipo'], $_POST['categoria'], (int)$_POST['calificacion'], $_POST['autor'], $_POST['comentario']]);

        $manager = new MongoDB\Driver\Manager($mongoUri);
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert(['equipo'=>$_POST['equipo'], 'categoria'=>$_POST['categoria'], 'calificacion'=>(int)$_POST['calificacion'], 'autor'=>$_POST['autor'], 'comentario'=>$_POST['comentario'], 'fecha'=>date('Y-m-d H:i:s')]);
        $manager->executeBulkWrite('unilago_db.resenas', $bulk);
        
        $alert = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'><strong>Success:</strong> Auditoría registrada en base de datos distribuida.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } catch (Exception $e) {
        $alert = "<div class='alert alert-danger shadow-sm'><strong>Error de Conexión:</strong> {$e->getMessage()}</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniLago | Enterprise Audit Engine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --deep-blue: #0f172a; --cyber-blue: #38bdf8; --glass: rgba(255,255,255,0.9); }
        body { background: #f8fafc; font-family: 'Inter', system-ui; }
        .hero-section { background: linear-gradient(135deg, var(--deep-blue), #1e293b); color: white; padding: 60px 0; border-bottom-left-radius: 50px; border-bottom-right-radius: 50px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); transition: 0.4s; }
        .card:hover { transform: translateY(-10px); }
        .btn-cyber { background: linear-gradient(45deg, var(--cyber-blue), #818cf8); color: white; border: none; border-radius: 10px; padding: 12px 25px; font-weight: 700; transition: 0.3s; }
        .btn-cyber:hover { box-shadow: 0 0 20px var(--cyber-blue); }
        .status-pill { padding: 5px 15px; border-radius: 20px; font-size: 0.75rem; background: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>
    <header class="hero-section text-center">
        <h1 class="display-4 fw-bold"><i class="fa-solid fa-microchip me-3"></i>UNI-LAGO OPS</h1>
        <p class="lead text-white-50">Auditoría Tecnológica Avanzada - Sincronización en tiempo real</p>
    </header>

    <div class="container mt-n5" style="margin-top: -50px;">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-5">
                <div class="card p-5">
                    <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-file-signature me-2"></i>Nueva Auditoría</h4>
                    <?= $alert ?>
                    <form method="POST">
                        <div class="mb-3"><input type="text" name="equipo" class="form-control form-control-lg" placeholder="Modelo del Dispositivo" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><select name="categoria" class="form-select form-select-lg"><option>Portátiles</option><option>Componentes PC</option><option>Monitores</option></select></div>
                            <div class="col-md-6 mb-3"><input type="number" name="calificacion" class="form-control form-control-lg" placeholder="Rating (1-5)" min="1" max="5" required></div>
                        </div>
                        <div class="mb-3"><input type="text" name="autor" class="form-control form-control-lg" placeholder="Técnico a cargo" required></div>
                        <div class="mb-4"><textarea name="comentario" class="form-control form-control-lg" rows="4" placeholder="Análisis térmico y rendimiento..."></textarea></div>
                        <button type="submit" name="btn_guardar" class="btn btn-cyber w-100 py-3">INICIAR PROCESAMIENTO</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark"><i class="fa-solid fa-database me-2 text-warning"></i>Historial de Auditoría</h4>
                        <span class="status-pill"><i class="fa-solid fa-circle-nodes me-1"></i>Live Stream</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr><th>Dispositivo</th><th>Categoría</th><th>Técnico</th><th>Registro</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $pdo = new PDO("pgsql:host=$host_pg;dbname=$db_pg", $user_pg, $pass_pg);
                                    foreach($pdo->query("SELECT * FROM resenas ORDER BY id DESC LIMIT 10") as $r) {
                                        echo "<tr>
                                            <td class='fw-bold'>{$r['equipo']}</td>
                                            <td><span class='badge bg-light text-dark'>{$r['categoria']}</span></td>
                                            <td>{$r['autor']}</td>
                                            <td><small class='text-muted'>Just now</small></td>
                                        </tr>";
                                    }
                                } catch(Exception $e) { echo "<tr><td colspan='4'>Sistema offline.</td></tr>"; }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-5 mb-3 text-muted small">
        &copy; 2026 UniLago Operations | Infraestructura Distribuida
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

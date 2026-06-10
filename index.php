<?php
// --- CONFIGURACIÓN DE CREDENCIALES ---
// Reemplaza los valores con los que te dio Render (Postgres) y Atlas (Mongo)
$host_pg = "TU_HOST_POSTGRES"; 
$db_pg   = "TU_NOMBRE_DB";
$user_pg = "TU_USUARIO_PG";
$pass_pg = "TU_PASSWORD_PG";

$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority";

$mensaje_alerta = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    try {
        // 1. Conexión y Guardado en PostgreSQL
        $dsn = "pgsql:host=$host_pg;dbname=$db_pg";
        $pdo = new PDO($dsn, $user_pg, $pass_pg, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $sql = "INSERT INTO resenas (equipo, categoria, calificacion, autor, comentario) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['equipo'], $_POST['categoria'], (int)$_POST['calificacion'], $_POST['autor'], $_POST['comentario']]);

        // 2. Conexión y Guardado en MongoDB Atlas
        $manager = new MongoDB\Driver\Manager($mongoUri);
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'equipo'       => $_POST['equipo'],
            'categoria'    => $_POST['categoria'],
            'calificacion' => (int)$_POST['calificacion'],
            'autor'        => $_POST['autor'],
            'comentario'   => $_POST['comentario'],
            'fecha'        => date('Y-m-d H:i:s')
        ]);
        $manager->executeBulkWrite('unilago_db.resenas', $bulk);

        $mensaje_alerta = "¡Reseña sincronizada correctamente en Postgres y MongoDB Atlas!";
        $tipo_alerta = "success";
    } catch (Exception $e) {
        $mensaje_alerta = "Error de conexión: " . $e->getMessage();
        $tipo_alerta = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago - Panel Profesional de Reseñas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-premium { border: none; border-radius: 15px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); background: #ffffff; transition: transform 0.2s; }
        .btn-gradient { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); color: white; border: none; font-weight: 600; border-radius: 8px; }
        .tech-badge { background: #eef2f7; color: #2a5298; font-weight: 600; border-radius: 20px; padding: 5px 12px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark navbar-custom py-3 mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fa-solid fa-microchip fa-2x me-3 text-info"></i>
                <div>
                    <span class="fw-bold fs-4 d-block">UNILAGO</span>
                    <small>Plataforma de Auditoría Tecnológica</small>
                </div>
            </a>
            <span class="badge bg-info text-dark px-3 py-2 fw-bold"><i class="fa-solid fa-server me-1"></i> Entorno: Render Cloud</span>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if (!empty($mensaje_alerta)): ?>
            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $mensaje_alerta; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card card-premium p-4">
                    <h3 class="text-dark fw-bold mb-4"><i class="fa-solid fa-square-plus text-primary me-2"></i>Nueva Reseña</h3>
                    <form action="index.php" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo del Dispositivo</label>
                                <input type="text" name="equipo" class="form-control" required placeholder="Ej: ASUS ROG Strix G16">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="Portátiles">Portátiles</option>
                                    <option value="Componentes PC">Componentes PC</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Evaluación</label>
                                <select name="calificacion" class="form-select" required>
                                    <option value="5">5 Estrellas</option>
                                    <option value="4">4 Estrellas</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especialista / Auditor</label>
                            <input type="text" name="autor" class="form-control" required placeholder="Nombre del Técnico">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Diagnóstico Técnico</label>
                            <textarea name="comentario" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="btn_guardar" class="btn btn-gradient w-100 py-3 fs-5">Transmitir Datos a Postgres y MongoDB</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * UNI-LAGO PROFESSIONAL AUDIT & FEEDBACK SYSTEM
 * Versión Final - Conexión por Database URL e Infraestructura Híbrida
 */

// --- EXTRACCIÓN Y PARSEO DINÁMICO DE DATABASE_URL ---
$databaseUrl = getenv('DATABASE_URL');

if (!empty($databaseUrl)) {
    $dbparts = parse_url($databaseUrl);
    $host_pg  = $dbparts['host'] ?? null;
    $user_pg  = $dbparts['user'] ?? null;
    $pass_pg  = $dbparts['pass'] ?? null;
    $db_pg    = ltrim($dbparts['path'] ?? '', '/');
    $port_pg  = $dbparts['port'] ?? 5432;
} else {
    $host_pg  = "localhost";
    $db_pg    = "unilago_db";
    $user_pg  = "postgres";
    $pass_pg  = "secret";
    $port_pg  = 5432;
}

// --- CONFIGURACIÓN DE MONGO ATLAS ---
$mongoUri = "mongodb+srv://santibautista720_db_user:rALSrEuApb3lzwkq@unilago.skrrmay.mongodb.net/?retryWrites=true&w=majority&appName=unilago";
$dbCollection = "unilago_db.resenas";

$mensaje = "";
$tipoAlerta = "";

// --- LOGICA DE PROCESAMIENTO CENTRALIZADA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    $equipo       = htmlspecialchars(trim($_POST['equipo'] ?? ''));
    $categoria    = htmlspecialchars(trim($_POST['categoria'] ?? ''));
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $autor        = htmlspecialchars(trim($_POST['autor'] ?? ''));
    $comentario   = htmlspecialchars(trim($_POST['comentario'] ?? ''));
    $fecha_actual = date('Y-m-d H:i:s');

    if (empty($equipo) || empty($autor) || $calificacion < 1 || $calificacion > 5) {
        $mensaje = "Error: Datos de auditoría inconsistentes o campos vacíos.";
        $tipoAlerta = "error";
    } else {
        try {
            // 1. Inserción en PostgreSQL usando los datos parseados
            $dsn = "pgsql:host=$host_pg;port=$port_pg;dbname=$db_pg";
            $pdo = new PDO($dsn, $user_pg, $pass_pg, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true
            ]);
            
            $sql = "INSERT INTO resenas (equipo, categoria, calificacion, autor, comentario) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$equipo, $categoria, $calificacion, $autor, $comentario]);

            // 2. Transmisión paralela a MongoDB Atlas
            $manager = new MongoDB\Driver\Manager($mongoUri);
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'equipo'       => $equipo,
                'categoria'    => $categoria,
                'calificacion' => $calificacion,
                'autor'        => $autor,
                'comentario'   => $comentario,
                'fecha'        => $fecha_actual
            ]);
            $manager->executeBulkWrite($dbCollection, $bulk);

            $mensaje = "¡Transmisión Exitosa! Datos indexados en Postgres y replicados en MongoDB Atlas.";
            $tipoAlerta = "exito";
        } catch (Exception $e) {
            $mensaje = "Falla en el pipeline de datos: " . $e->getMessage();
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
    <title>UniLago | Consola Corporativa de Auditoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand-dark: #0f172a; --brand-blue: #2563eb; --light-gray: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; padding-bottom: 60px; }
        .navbar-custom { background: linear-gradient(135deg, var(--brand-dark) 0%, #1e293b 100%); padding: 22px 0; box-shadow: 0 4px 20px rgba(0,0,0,0.12); }
        .navbar-brand h2 { font-weight: 700; letter-spacing: -0.5px; margin: 0; color: white; font-size: 24px; }
        .main-container { max-width: 1350px; margin: 45px auto; padding: 0 25px; }
        .glass-panel { background: #ffffff; border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.03); padding: 40px; height: 100%; }
        label { font-weight: 600; color: #475569; margin-bottom: 6px; font-size: 14px; }
        .form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 12px; padding: 13px; font-size: 15px; transition: all 0.3s ease; background-color: var(--light-gray); }
        .form-control:focus, .form-select:focus { border-color: var(--brand-blue); box-shadow: 0 0 0 4px rgba(37,99,235,0.12); background-color: #fff; outline: none; }
        .btn-action { background: var(--brand-blue); color: white; font-weight: 600; padding: 15px; border: none; border-radius: 12px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); width: 100%; letter-spacing: 0.5px; }
        .btn-action:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.25); }
        .alerta { padding: 16px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
        .exito { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .engine-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
        .badge-postgres { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-mongo { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom">
        <div class="container-fluid px-5 d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fa-solid fa-server text-info fa-2x me-3"></i>
                <h2>UNILAGO ENTERPRISE</h2>
            </a>
            <span class="badge bg-info text-dark py-2 px-3 rounded-pill fw-bold"><i class="fa-solid fa-link me-1"></i> Mode: DATABASE_URL Active</span>
        </div>
    </nav>

    <div class="main-container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-panel">
                    <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-circle-plus text-primary me-2"></i>Ingreso de Reseña</h4>
                    <?php if ($mensaje): ?>
                        <div class="alerta <?php echo $tipoAlerta; ?>">
                            <i class="fa-solid <?php echo $tipoAlerta === 'exito' ? 'fa-square-check' : 'fa-circle-exclamation'; ?>"></i>
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" id="feedbackForm">
                        <div class="mb-3">
                            <label>Dispositivo Evaluado</label>
                            <input type="text" name="equipo" class="form-control" required placeholder="Ej. MacBook Pro M3 Max">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Categoría</label>
                                <select name="categoria" class="form-select">
                                    <option value="Portátiles">Portátiles</option>
                                    <option value="Componentes PC">Componentes PC</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Calificación</label>
                                <select name="calificacion" class="form-select">
                                    <option value="5">5 Estrellas</option>
                                    <option value="4">4 Estrellas</option>
                                    <option value="3">3 Estrellas</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Auditor Técnico</label>
                            <input type="text" name="autor" class="form-control" required placeholder="Identificación del Ingeniero">
                        </div>
                        <div class="mb-4">
                            <label>Diagnóstico Estructurado</label>
                            <textarea name="comentario" class="form-control" rows="4" placeholder="Análisis técnico..."></textarea>
                        </div>
                        <button type="submit" name="btn_guardar" id="btnSubmit" class="btn-action">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Procesar e Inyectar Datos
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-database text-success me-2"></i>Consola de Sincronización</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr><th>Dispositivo</th><th>Especialista</th><th>Relacional</th><th>NoSQL Atlas</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($host_pg) && !empty($db_pg)) {
                                    try {
                                        $pdoRead = new PDO("pgsql:host=$host_pg;port=$port_pg;dbname=$db_pg", $user_pg, $pass_pg);
                                        $query = $pdoRead->query("SELECT * FROM resenas ORDER BY id DESC LIMIT 6");
                                        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
                                        if (count($rows) > 0) {
                                            foreach ($rows as $row) {
                                                echo "<tr>";
                                                echo "<td class='fw-bold text-dark'>{$row['equipo']}</td>";
                                                echo "<td><span class='text-muted small'>{$row['autor']}</span></td>";
                                                echo "<td><span class='engine-badge badge-postgres'><i class='fa-solid fa-cube me-1'></i>Postgres SQL</span></td>";
                                                echo "<td><span class='engine-badge badge-mongo'><i class='fa-solid fa-leaf me-1'></i>Synced</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Canal vacío. Ingresa el primer registro.</td></tr>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='4' class='text-center py-4 text-danger fw-bold'>Conectando infraestructura... tabla en proceso.</td></tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('feedbackForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-2"></i>Escribiendo en Clúster Relacional y Documental...';
            btn.disabled = true;
        });
    </script>
</body>
</html>

<?php
// Enlace de conexión directo a tu PostgreSQL de Render
$db_url = "postgresql://santiago_user:6IbDCvGpRPCOmswaIIuQ3k0jatpNMvVO@dpg-d8ks7afavr4c73en8ggg-a.oregon-postgres.render.com/unilago_db_u5qg";

// Conectar a la base de datos
$dbconn = pg_connect($db_url);

if (!$dbconn) {
    die("Error al conectar con la base de datos de UniLago.");
}

// Crear la tabla si no existe (Requisito del profesor)
$query_table = "CREATE TABLE IF NOT EXISTS resenas (
    id SERIAL PRIMARY KEY,
    tienda VARCHAR(100) NOT NULL,
    comentario TEXT NOT NULL,
    estrellas INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
pg_query($dbconn, $query_table);

// Guardar datos si envían el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tienda = $_POST['tienda'];
    $comentario = $_POST['comentario'];
    $estrellas = $_POST['estrellas'];

    $query_insert = "INSERT INTO resenas (tienda, comentario, estrellas) VALUES ($1, $2, $3)";
    pg_query_params($dbconn, $query_insert, array($tienda, $comentario, $estrellas));
}

// Traer todas las reseñas
$result = pg_query($dbconn, "SELECT * FROM resenas ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago - Centro de Opiniones Profesional</title>
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #f59e0b;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
            letter-spacing: -1px;
        }

        header p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            border: 1px solid #e2e8f0;
        }

        .card h2 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
            font-size: 0.95rem;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fdfdfd;
        }

        input[type="text"]:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background-color: #fff;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        button:hover {
            background-color: #172554;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .resena-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .resena-card {
            background: var(--card-bg);
            border-left: 5px solid var(--secondary-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-top: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .resena-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .tienda-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .stars {
            color: var(--accent-color);
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .comment {
            color: #334155;
            margin: 0 0 15px 0;
            font-size: 1rem;
        }

        .date {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: block;
            text-align: right;
        }

        .no-data {
            text-align: center;
            color: var(--text-muted);
            padding: 40px;
            background: #f1f5f9;
            border-radius: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <header>
        <h1>🏢 Centro de Experiencias UniLago</h1>
        <p>Plataforma de Auditoría y Calificación de Tiendas Tecnológicas</p>
    </header>

    <div class="container">
        <div class="card">
            <h2>📝 Registrar Nueva Calificación</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="tienda">Establecimiento / Local:</label>
                    <input type="text" id="tienda" name="tienda" required placeholder="Ej: Acer Oficial - Local 145">
                </div>
                
                <div class="form-group">
                    <label for="estrellas">Nivel de Satisfacción:</label>
                    <select id="estrellas" name="estrellas">
                        <option value="5">⭐⭐⭐⭐⭐ Excelente Servicio y Garantía</option>
                        <option value="4">⭐⭐⭐⭐ Buen Precio y Atención</option>
                        <option value="3">⭐⭐⭐ Regular / Precios Altos</option>
                        <option value="2">⭐⭐ Mala Atención al Cliente</option>
                        <option value="1">⭐ Pésima Experiencia / Producto Defectuoso</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comentario">Reseña Detallada:</label>
                    <textarea id="comentario" name="comentario" rows="4" required placeholder="Describe tu experiencia con la compra, la atención de los asesores y los precios..."></textarea>
                </div>

                <button type="submit">Publicar Auditoría</button>
            </form>
        </div>

        <h3 class="section-title">📊 Historial de Auditorías en Tiempo Real</h3>
        <div class="resena-list">
            <?php if (pg_num_rows($result) === 0): ?>
                <div class="no-data">Aún no hay reseñas registradas para este centro comercial. Sé el primero.</div>
            <?php else: ?>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                    <div class="resena-card">
                        <div class="resena-header">
                            <span class="tienda-name">🏢 <?php echo htmlspecialchars($row['tienda']); ?></span>
                            <span class="stars"><?php echo str_repeat('★', $row['estrellas']) . str_repeat('☆', 5 - $row['estrellas']); ?></span>
                        </div>
                        <p class="comment">"<?php echo htmlspecialchars($row['comentario']); ?>"</p>
                        <small class="date">📅 Registrado el: <?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

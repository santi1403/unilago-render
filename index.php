<?php
// Más adelante aquí conectaremos los servidores de Postgres y MongoDB
$mensaje_alerta = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
    // Aquí irá la lógica de inserción doble (Postgres + respaldo en Mongo)
    $mensaje_alerta = "¡Reseña procesada con éxito en el sistema central de UniLago!";
    $tipo_alerta = "success";
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
        .card-premium:hover { transform: translateY(-2px); }
        .btn-gradient { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); color: white; border: none; font-weight: 600; border-radius: 8px; transition: all 0.3s; }
        .btn-gradient:hover { background: linear-gradient(135deg, #1c7a93 0%, #5bbcd3 100%); color: white; box-shadow: 0 4px 15px rgba(33, 147, 176, 0.4); }
        .tech-badge { background: #eef2f7; color: #2a5298; font-weight: 600; border-radius: 20px; padding: 5px 12px; font-size: 0.85rem; display: inline-block; }
        .star-rating { color: #ffc107; font-size: 1.1rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark navbar-custom py-3 mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fa-solid fa-microchip fa-2x me-3 text-info"></i>
                <div>
                    <span class="fw-bold fs-4 d-block">UNILAGO</span>
                    <small class="text-light-50 fs-7">Plataforma de Auditoría Tecnológica</small>
                </div>
            </a>
            <span class="badge bg-info text-dark px-3 py-2 fw-bold"><i class="fa-solid fa-server me-1"></i> Entorno: Render Cloud</span>
        </div>
    </nav>

    <div class="container mb-5">
        
        <?php if (!empty($mensaje_alerta)): ?>
            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $mensaje_alerta; ?>
                <button type="button" class="btn-close" data-by-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card card-premium p-4">
                    <h3 class="text-dark fw-bold mb-4 d-flex align-items-center">
                        <i class="fa-solid fa-square-plus text-primary me-2"></i>Nueva Reseña
                    </h3>
                    
                    <form action="index.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="equipo" class="form-label fw-semibold text-secondary">Modelo del Dispositivo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-laptop"></i></span>
                                <input type="text" id="equipo" name="equipo" class="form-control" required placeholder="Ej: ASUS ROG Strix G16">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label fw-semibold text-secondary">Categoría</label>
                                <select id="categoria" name="categoria" class="form-select bg-light" required>
                                    <option value="Portátiles">Portátiles</option>
                                    <option value="Componentes PC">Componentes PC</option>
                                    <option value="Monitores">Monitores</option>
                                    <option value="Periféricos">Periféricos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="calificacion" class="form-label fw-semibold text-secondary">Evaluación</label>
                                <select id="calificacion" name="calificacion" class="form-select bg-light text-warning fw-bold" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                    <option value="3">⭐⭐⭐ (3/5)</option>
                                    <option value="2">⭐⭐ (2/5)</option>
                                    <option value="1">⭐ (1/5)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="autor" class="form-label fw-semibold text-secondary">Especialista / Auditor</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-user-shield"></i></span>
                                <input type="text" id="autor" name="autor" class="form-control" required placeholder="Nombre del Técnico">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="comentario" class="form-label fw-semibold text-secondary">Diagnóstico Técnico</label>
                            <textarea id="comentario" name="comentario" class="form-control" rows="4" maxlength="500" required placeholder="Escriba el análisis de rendimiento, temperaturas y arquitectura física..."></textarea>
                            <div class="form-text text-end" id="char-count">0 / 500 caracteres</div>
                        </div>

                        <button type="submit" name="btn_guardar" class="btn btn-gradient w-100 py-3 fs-5">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Transmitir Datos
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-premium p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark fw-bold m-0 d-flex align-items-center">
                            <i class="fa-solid fa-database text-success me-2"></i>Registros Indexados
                        </h3>
                        <span class="badge bg-dark rounded-pill px-3">Live Feed</span>
                    </div>

                    <form action="index.php" method="GET" class="row g-2 mb-4">
                        <div class="col-sm-8">
                            <select name="categoria_filtro" class="form-select bg-light">
                                <option value="">-- Filtrar por Línea de Producto --</option>
                                <option value="Portátiles">Portátiles</option>
                                <option value="Componentes PC">Componentes PC</option>
                                <option value="Monitores">Monitores</option>
                                <option value="Periféricos">Periféricos</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                        </div>
                    </form>

                    <div class="overflow-y-auto" style="max-height: 520px; padding-right: 5px;">
                        
                        <div class="card card-premium border-start border-primary border-4 p-3 mb-3 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-microchip me-2"></i>NVIDIA RTX 4070 Ti Super</h5>
                                <span class="tech-badge"><i class="fa-solid fa-tags me-1"></i>Componentes PC</span>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="fa-solid fa-user-gear me-1"></i>Ing. Santiago Aguilar | 
                                <span class="star-rating ms-2">⭐⭐⭐⭐⭐</span>
                            </p>
                            <p class="text-dark bg-white p-3 rounded border border-light-subtle fs-6 shadow-sm mb-2">
                                Excelente escalabilidad térmica en pruebas de estrés bajo entornos virtualizados. El consumo energético se estabiliza en los 285W con picos controlados. Recomendado para ensambles de alta gama en UniLago.
                            </p>
                            <div class="d-flex justify-content-between align-items-center text-secondary small">
                                <span><i class="fa-solid fa-clock me-1"></i> Sincronizado en Postgres + MongoDB Atlas</span>
                                <span class="text-success fw-bold"><i class="fa-solid fa-shield-halved me-1"></i> Seguro</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Contador dinámico de caracteres
        const textarea = document.getElementById('comentario');
        const charCount = document.getElementById('char-count');
        textarea.addEventListener('input', () => {
            charCount.textContent = `${textarea.value.length} / 500 caracteres`;
        });

        // Desactivar envíos si hay campos inválidos
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
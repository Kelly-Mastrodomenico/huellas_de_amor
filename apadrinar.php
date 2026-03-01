<?php
$tituloPagina = 'Apadrinar — Huellas de Amor';
require_once 'templates/header.php';

// Obtener mascotas disponibles para apadrinar
$mascotasApadrinar = [];
try {
    $stmt = $conexion->prepare(
        "SELECT m.*,
            (SELECT f.ruta_foto FROM `fotos_mascotas` f
             WHERE f.id_mascota = m.id AND f.es_principal = 1
             LIMIT 1) AS foto_principal,
            (SELECT COUNT(*) FROM `apadrinamientos` a
             WHERE a.id_mascota = m.id AND a.activo = 1) AS total_padrinos
         FROM `mascotas` m
         WHERE m.activo = 1 AND m.estado != 'adoptado'
         ORDER BY RAND()
         LIMIT 8"
    );
    $stmt->execute();
    $mascotasApadrinar = $stmt->fetchAll();
} catch (PDOException $e) {
    $mascotasApadrinar = [];
}

// Planes definidos
$planes = [
    'basico' => [
        'nombre'   => 'Padrino Básico',
        'precio'   => 5,
        'icono'    => 'fa-heart',
        'color'    => 'turquesa',
        'beneficios' => [
            'Foto mensual de tu apadrinado',
            'Certificado digital de apadrinamiento',
            'Actualizaciones de salud mensuales',
            'Fondos para alimento y cuidados básicos',
        ]
    ],
    'medio' => [
        'nombre'   => 'Padrino Amigo',
        'precio'   => 15,
        'icono'    => 'fa-star',
        'color'    => 'coral',
        'popular'  => true,
        'beneficios' => [
            'Todo lo del plan Básico',
            'Visitas al refugio los fines de semana',
            'Reportaje fotográfico trimestral',
            'Fondos para vacunas y veterinario',
            'Desgravación fiscal IRPF hasta 80%',
        ]
    ],
    'completo' => [
        'nombre'   => 'Padrino Especial',
        'precio'   => 30,
        'icono'    => 'fa-crown',
        'color'    => 'oscuro',
        'beneficios' => [
            'Todo lo del plan Amigo',
            'Paseos exclusivos con tu apadrinado',
            'Videollamada mensual con el equipo',
            'Cubre esterilización y tratamientos',
            'Prioridad de adopción si lo deseas',
            'Desgravación fiscal IRPF hasta 80%',
        ]
    ],
];
?>

<!-- HERO -->
<section class="apadrinar-hero">
    <div class="apadrinar-hero-overlay"></div>
    <div class="apadrinar-hero-contenido">
        <span class="apadrinar-hero-tag">Programa de Apadrinamiento</span>
        <h1>Sé su <span>Padrino</span></h1>
        <p>Si no puedes adoptarlos, puedes ayudarles. Tu aportación mensual cubre su alimentación, vacunas y cuidados mientras esperan un hogar.</p>
        <a href="#mascotas" class="btn-coral">
            <i class="fa-solid fa-heart"></i> Elige tu apadrinado
        </a>
    </div>
</section>

<!-- COMO FUNCIONA -->
<section class="seccion">
    <div class="contenedor">
        <div class="seccion-titulo">
            <h2>¿Cómo funciona?</h2>
            <p>Apadrinar es fácil, flexible y marca una diferencia real en la vida de un animal.</p>
        </div>
        <div class="apadrinar-pasos">
            <div class="paso-item">
                <div class="paso-numero">1</div>
                <i class="fa-solid fa-paw"></i>
                <h3>Elige tu apadrinado</h3>
                <p>Explora nuestras mascotas y elige aquella que más te llame el corazón.</p>
            </div>
            <div class="paso-flecha"><i class="fa-solid fa-chevron-right"></i></div>
            <div class="paso-item">
                <div class="paso-numero">2</div>
                <i class="fa-solid fa-star"></i>
                <h3>Selecciona tu plan</h3>
                <p>Desde 5€/mes. Elige el plan que mejor se adapte a tus posibilidades.</p>
            </div>
            <div class="paso-flecha"><i class="fa-solid fa-chevron-right"></i></div>
            <div class="paso-item">
                <div class="paso-numero">3</div>
                <i class="fa-solid fa-envelope"></i>
                <h3>Recibe actualizaciones</h3>
                <p>Te enviamos fotos y noticias de tu apadrinado cada mes.</p>
            </div>
            <div class="paso-flecha"><i class="fa-solid fa-chevron-right"></i></div>
            <div class="paso-item">
                <div class="paso-numero">4</div>
                <i class="fa-solid fa-house-heart"></i>
                <h3>Visítalo cuando quieras</h3>
                <p>Puedes visitarlo y pasear con él los fines de semana en el refugio.</p>
            </div>
        </div>
    </div>
</section>

<!-- PLANES -->
<section class="seccion seccion-gris" id="planes">
    <div class="contenedor">
        <div class="seccion-titulo">
            <h2>Planes de Apadrinamiento</h2>
            <p>Elige la aportación que puedas. Toda ayuda es bienvenida y desgrava en el IRPF.</p>
        </div>
        <div class="apadrinar-planes">
            <?php foreach ($planes as $clavePlan => $plan) { ?>
            <div class="plan-card <?php echo isset($plan['popular']) ? 'plan-popular' : ''; ?>">
                <?php if (isset($plan['popular'])) { ?>
                <div class="plan-badge-popular">Más elegido</div>
                <?php } ?>
                <div class="plan-icono plan-icono-<?php echo $plan['color']; ?>">
                    <i class="fa-solid <?php echo $plan['icono']; ?>"></i>
                </div>
                <h3><?php echo $plan['nombre']; ?></h3>
                <div class="plan-precio">
                    <span class="plan-monto"><?php echo $plan['precio']; ?>€</span>
                    <span class="plan-periodo">/mes</span>
                </div>
                <ul class="plan-beneficios">
                    <?php foreach ($plan['beneficios'] as $beneficio) { ?>
                    <li><i class="fa-solid fa-check"></i> <?php echo $beneficio; ?></li>
                    <?php } ?>
                </ul>
                <a href="#mascotas" class="plan-btn plan-btn-<?php echo $plan['color']; ?>">
                    Elegir este plan
                </a>
            </div>
            <?php } ?>
        </div>
        <p class="apadrinar-fiscal">
            <i class="fa-solid fa-circle-info"></i>
            Las donaciones a protectoras de animales desgravan hasta un <strong>80% en los primeros 150€</strong> en la declaración del IRPF.
        </p>
    </div>
</section>

<!-- MASCOTAS PARA APADRINAR -->
<section class="seccion" id="mascotas">
    <div class="contenedor">
        <div class="seccion-titulo">
            <h2>Mascotas que Necesitan Padrino</h2>
            <p>Cada una tiene una historia. ¿Cuál te toca el corazón?</p>
        </div>

        <?php if (!empty($mascotasApadrinar)) { ?>
        <div class="grid-mascotas">
            <?php foreach ($mascotasApadrinar as $mascota) {
                $foto = $mascota['foto_principal'];
                if ($foto && !str_starts_with($foto, 'http')) {
                    $foto = ltrim($foto, '/');
                }
                if (!$foto) {
                    $foto = 'https://picsum.photos/400/300?random=' . $mascota['id'];
                }
            ?>
            <div class="tarjeta-mascota">
                <div class="tarjeta-img">
                    <img src="<?php echo htmlspecialchars($foto); ?>"
                         alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
                    <span class="badge-estado badge badge-<?php echo $mascota['estado']; ?>">
                        <?php echo textoEstado($mascota['estado']); ?>
                    </span>
                    <?php if ($mascota['total_padrinos'] > 0) { ?>
                    <span class="badge-padrinos">
                        <i class="fa-solid fa-heart"></i> <?php echo $mascota['total_padrinos']; ?> padrino<?php echo $mascota['total_padrinos'] > 1 ? 's' : ''; ?>
                    </span>
                    <?php } ?>
                </div>
                <div class="tarjeta-cuerpo">
                    <h3><?php echo htmlspecialchars($mascota['nombre']); ?></h3>
                    <span class="tarjeta-edad">
                        <?php echo formatearEdad($mascota['edad_anios'], $mascota['edad_meses']); ?>
                    </span>
                    <div class="tarjeta-datos">
                        <span><i class="fa-solid fa-ruler"></i><?php echo htmlspecialchars($mascota['tamanio']); ?></span>
                        <span><i class="fa-solid fa-venus-mars"></i><?php echo htmlspecialchars($mascota['sexo']); ?></span>
                        <span><i class="fa-solid fa-paw"></i><?php echo htmlspecialchars($mascota['especie']); ?></span>
                    </div>
                </div>
                <div class="tarjeta-footer">
                    <a href="<?php echo estaLogueado()
                        ? 'formulario_apadrinamiento.php?id=' . $mascota['id']
                        : 'login.php'; ?>"
                       class="btn-coral">
                        <i class="fa-solid fa-heart"></i> Apadrinar
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <p class="sin-resultados">No hay mascotas disponibles en este momento.</p>
        <?php } ?>
    </div>
</section>

<!-- BENEFICIOS EXTRA -->
<section class="seccion seccion-gris">
    <div class="contenedor">
        <div class="seccion-titulo">
            <h2>¿Por qué apadrinar?</h2>
        </div>
        <div class="apadrinar-beneficios">
            <div class="beneficio-item">
                <i class="fa-solid fa-syringe"></i>
                <h3>Cuidados garantizados</h3>
                <p>Tu aportación cubre vacunas, esterilización, veterinario y alimentación diaria.</p>
            </div>
            <div class="beneficio-item">
                <i class="fa-solid fa-images"></i>
                <h3>Siempre informado</h3>
                <p>Recibe fotos y noticias mensuales sobre la evolución y bienestar de tu apadrinado.</p>
            </div>
            <div class="beneficio-item">
                <i class="fa-solid fa-rotate"></i>
                <h3>Total flexibilidad</h3>
                <p>Si el animal es adoptado, puedes elegir otro, pausar o finalizar tu aportación cuando quieras.</p>
            </div>
            <div class="beneficio-item">
                <i class="fa-solid fa-file-invoice"></i>
                <h3>Ventajas fiscales</h3>
                <p>Desgrava hasta un 80% en los primeros 150€ de tu declaración del IRPF.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="seccion-cta">
    <div class="contenedor">
        <h2>¿Listo para ser padrino?</h2>
        <p>Miles de animales necesitan tu ayuda hoy. Con solo 5€ al mes puedes cambiar una vida.</p>
        <div class="cta-botones">
            <a href="#mascotas" class="btn-coral">
                <i class="fa-solid fa-heart"></i> Elegir mi apadrinado
            </a>
            <a href="donaciones.php" class="btn-blanco">
                Hacer una donación
            </a>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>
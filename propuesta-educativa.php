<?php
// Página generada sobre partials/header.php + partials/footer.php.
// El menú se edita en partials/menu.php; los datos de contacto,
// desde el panel (Datos de contacto).
$page_title      = 'Propuesta Educativa';
$page_desc       = 'Proyecto pedagógico del Colegio Parroquial Juan XXIII: formación integral, valores y acompañamiento en los tres niveles.';
$nav_active      = 'institucional';
$nav_active_link = 'propuesta-educativa.php';
require __DIR__ . '/partials/header.php';
?>
  

  <?php page_hero(['Inicio' => 'index.php', 'Institucional' => null, 'Propuesta Educativa' => null], 'Institucional', 'Una <em>educación integral</em> centrada en la persona', 'Formamos personas libres, críticas y solidarias, integrando la excelencia académica con los valores del Evangelio.'); ?>

  <section class="content-section">
    <div class="split reverse">
      <div class="prose">
        <h2>Nuestro proyecto pedagógico</h2>
        <p>Creemos en una escuela que pone al estudiante en el centro del aprendizaje. Por eso trabajamos con metodologías activas, acompañamiento personalizado y una mirada atenta a la diversidad de cada grupo.</p>
        <p>La articulación entre niveles garantiza una trayectoria coherente, donde cada etapa prepara para la siguiente sin perder la identidad propia de cada edad.</p>
      </div>
      <div class="split-media">
        <div class="image-placeholder split-img"><span>Foto del aula</span></div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="content-section narrow">
      <div class="section-header">
        <span class="section-tag">Nuestros pilares</span>
        <h2 class="section-title">Sobre qué se apoya nuestra propuesta</h2>
      </div>
      <div class="pillar">
        <div class="p-num">01</div>
        <div>
          <h3>Excelencia académica</h3>
          <p>Contenidos actualizados, docentes formados y una exigencia adaptada a cada etapa para que cada estudiante alcance su máximo potencial.</p>
        </div>
      </div>
      <div class="pillar">
        <div class="p-num">02</div>
        <div>
          <h3>Formación en valores</h3>
          <p>El respeto, la solidaridad y el compromiso atraviesan todas las áreas, en sintonía con la identidad cristiana de la institución.</p>
        </div>
      </div>
      <div class="pillar">
        <div class="p-num">03</div>
        <div>
          <h3>Tecnología e innovación</h3>
          <p>Integramos herramientas digitales y proyectos interdisciplinarios que preparan a los alumnos para los desafíos del mundo actual.</p>
        </div>
      </div>
      <div class="pillar">
        <div class="p-num">04</div>
        <div>
          <h3>Idiomas y apertura al mundo</h3>
          <p>La enseñanza del inglés desde el Nivel Inicial amplía las oportunidades académicas y laborales de nuestros egresados.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="content-section">
    <div class="card-grid">
      <a href="nivel-inicial.php" class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div><h3>Nivel Inicial</h3><p>Juego, descubrimiento y primeros aprendizajes.</p></a>
      <a href="nivel-primario.php" class="info-card"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div><h3>Nivel Primario</h3><p>Hábitos de estudio, valores y conocimiento.</p></a>
      <a href="nivel-secundario.php" class="info-card blue"><div class="ic-icon"><svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div><h3>Nivel Secundario</h3><p>Orientada y técnica, con proyección a futuro.</p></a>
    </div>
  </section>

  
<?php require __DIR__ . '/partials/footer.php';

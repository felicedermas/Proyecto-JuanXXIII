<?php
// ============================================================
//  partials/helpers.php
//  Componentes de vista reutilizables del sitio público.
// ============================================================

/**
 * Hero de página interna, con la miga de pan INTEGRADA adentro.
 *
 * Antes la miga de pan era una franja blanca suelta entre el header
 * azul y el hero azul: quedaba una banda clara de 2 cm cortando el
 * bloque oscuro. Ahora va dentro del hero, sobre el fondo azul.
 *
 * @param array  $miga    ['Inicio' => 'index.php', 'Institucional' => null, 'Historia' => null]
 *                        (valor null = texto sin enlace, se usa para la página actual)
 * @param string $eyebrow Texto chico sobre el título
 * @param string $titulo  HTML del título (admite <em> para el acento)
 * @param string $bajada  Párrafo bajo el título
 */
function page_hero(array $miga, string $eyebrow, string $titulo, string $bajada = ''): void {
    ?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <?php if ($miga): ?>
      <nav class="breadcrumb breadcrumb--hero" aria-label="Ruta de navegación">
        <?php $i = 0; $ultimo = count($miga) - 1;
        foreach ($miga as $texto => $href): ?>
          <?php if ($href !== null): ?>
            <a href="<?= e($href) ?>"><?= e($texto) ?></a>
          <?php else: ?>
            <span class="bc-actual" <?= $i === $ultimo ? 'aria-current="page"' : '' ?>><?= e($texto) ?></span>
          <?php endif; ?>
          <?php if ($i < $ultimo): ?><span class="bc-sep" aria-hidden="true">›</span><?php endif; ?>
        <?php $i++; endforeach; ?>
      </nav>
      <?php endif; ?>

      <?php if ($eyebrow !== ''): ?><p class="page-eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
      <h1><?= $titulo ?></h1>
      <?php if ($bajada !== ''): ?><p><?= $bajada ?></p><?php endif; ?>
    </div>
  </section>
    <?php
}

/**
 * Miga de pan suelta, para páginas que NO tienen hero (inscripciones,
 * fichas de novedad, etc.). Se dibuja como banda clara con borde, no
 * como texto flotando sobre el blanco.
 */
function breadcrumb(array $miga): void {
    if (!$miga) return;
    ?>
  <nav class="breadcrumb breadcrumb--banda" aria-label="Ruta de navegación">
    <div class="breadcrumb-inner">
      <?php $i = 0; $ultimo = count($miga) - 1;
      foreach ($miga as $texto => $href): ?>
        <?php if ($href !== null): ?>
          <a href="<?= e($href) ?>"><?= e($texto) ?></a>
        <?php else: ?>
          <span class="bc-actual" <?= $i === $ultimo ? 'aria-current="page"' : '' ?>><?= e($texto) ?></span>
        <?php endif; ?>
        <?php if ($i < $ultimo): ?><span class="bc-sep" aria-hidden="true">›</span><?php endif; ?>
      <?php $i++; endforeach; ?>
    </div>
  </nav>
    <?php
}

/** Número de teléfono listo para un href="tel:" (sin espacios ni guiones). */
function tel_href(string $clave = 'telefono_link'): string {
    return preg_replace('/[^0-9+]/', '', cfg($clave)) ?? '';
}

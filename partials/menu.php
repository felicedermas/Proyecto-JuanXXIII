<?php
// ============================================================
//  partials/menu.php
//  Estructura del menú principal, definida UNA sola vez.
//  Para agregar, sacar o renombrar una página del menú se toca
//  solamente este archivo y se actualizan las 30 páginas.
// ============================================================

/**
 * Cada sección: clave => [titulo, cols(bool), items[]]
 *             o clave => [titulo, href]  → enlace directo, sin desplegable
 * Cada item: [href, texto, subtexto] · ['divider'] para la línea separadora
 *            ['heading', 'Título de columna'] abre una columna (solo si cols=true)
 */
const MENU_PRINCIPAL = [

    'institucional' => [
        'titulo' => 'Institucional',
        'cols'   => false,
        'items'  => [
            ['historia.php',            'Historia',             'Nuestra trayectoria desde 1962'],
            ['autoridades.php',         'Autoridades',          'Equipo directivo y de gestión'],
            ['propuesta-educativa.php', 'Propuesta Educativa',  'Proyecto pedagógico institucional'],
            ['divider'],
            ['becas.php',               'Becas',                'Sistema de ayudas económicas'],
        ],
    ],

    'niveles' => [
        'titulo' => 'Niveles',
        'cols'   => false,
        'items'  => [
            ['nivel-inicial.php',   'Nivel Inicial',            'Sala de 3, 4 y 5'],
            ['nivel-primario.php',  'Nivel Primario',           '1° a 6° grado'],
            ['divider'],
            ['nivel-secundario.php','Sec. Orientada / Técnica', 'Bachillerato · 6 o 7 años'],
        ],
    ],

    // Enlace directo (sin desplegable): la página reúne los botones
    // a cada formulario. Los formularios se definen en partials/inscripciones.php
    'inscripciones' => [
        'titulo' => 'Inscripciones',
        'href'   => 'inscripciones.php',
    ],

    'comunidad' => [
        'titulo' => 'Comunidad',
        'cols'   => true,
        'items'  => [
            ['heading', 'Participación'],
            ['centro-estudiantes.php',      'Centro de Estudiantes',      'Voz y participación estudiantil'],
            ['feria-ciencias.php',          'Feria Anual de Ciencias',    'Proyectos e innovación'],
            ['pastoral.php',                'Pastoral',                   'Vida espiritual y solidaria'],
            ['egresados.php',               'Egresados',                  'Historias de ex-alumnos'],
            ['psicopedagogia.php',          'Equipo de Psicopedagogía',   'Acompañamiento y orientación'],
            ['heading', 'Vida escolar'],
            ['deportes.php',                'Deportes',                   'Disciplinas y equipos'],
            ['trabaja-con-nosotros.php',    'Trabajá con Nosotros',       'Sumate a nuestro equipo'],
            ['recorrido-360.php',           'Recorrido Virtual 360°',     'Conocé las instalaciones'],
            ['vinculos-institucionales.php','Vínculos Institucionales',   'Instituciones y empresas aliadas'],
            ['plataforma.php',              'Plataforma',                 'Acceso a Xhendra'],
        ],
    ],

    'contacto' => [
        'titulo' => 'Contacto',
        'cols'   => false,
        'items'  => [
            ['contacto.php',  'Contacto',             'Escribinos o llamanos'],
            ['ubicacion.php', 'Ubicación en el Mapa', 'Cómo llegar al colegio'],
        ],
    ],
];

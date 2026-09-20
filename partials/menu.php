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
            ['historia',            'Historia',             'Nuestra trayectoria desde 1962'],
            ['autoridades',         'Autoridades',          'Equipo directivo y de gestión'],
            ['propuesta-educativa', 'Propuesta Educativa',  'Proyecto pedagógico institucional'],
            ['divider'],
            ['becas',               'Becas',                'Sistema de ayudas económicas'],
        ],
    ],

    'niveles' => [
        'titulo' => 'Niveles',
        'cols'   => false,
        'items'  => [
            ['nivel-inicial',   'Nivel Inicial',            'Sala de 3, 4 y 5'],
            ['nivel-primario',  'Nivel Primario',           '1° a 6° grado'],
            ['divider'],
            ['nivel-secundario','Sec. Orientada / Técnica', 'Bachillerato · 6 o 7 años'],
        ],
    ],

    // Enlace directo (sin desplegable): la página reúne los botones
    // a cada formulario. Los formularios se definen en partials/inscripciones.php
    'inscripciones' => [
        'titulo' => 'Inscripciones',
        'href'   => 'inscripciones',
    ],

    'comunidad' => [
        'titulo' => 'Comunidad',
        'cols'   => true,
        'items'  => [
            ['heading', 'Participación'],
            ['centro-estudiantes',      'Centro de Estudiantes',      'Voz y participación estudiantil'],
            ['feria-ciencias',          'Feria Anual de Ciencias',    'Proyectos e innovación'],
            ['pastoral',                'Pastoral',                   'Vida espiritual y solidaria'],
            ['egresados',               'Egresados',                  'Historias de ex-alumnos'],
            ['psicopedagogia',          'Equipo de Psicopedagogía',   'Acompañamiento y orientación'],
            ['heading', 'Vida escolar'],
            ['deportes',                'Deportes',                   'Disciplinas y equipos'],
            ['trabaja-con-nosotros',    'Trabajá con Nosotros',       'Sumate a nuestro equipo'],
            ['recorrido-360',           'Recorrido Virtual 360°',     'Conocé las instalaciones'],
            ['vinculos-institucionales','Vínculos Institucionales',   'Instituciones y empresas aliadas'],
            ['plataforma',              'Plataforma',                 'Acceso a Xhendra'],
        ],
    ],

    'contacto' => [
        'titulo' => 'Contacto',
        'cols'   => false,
        'items'  => [
            ['contacto',  'Contacto',             'Escribinos o llamanos'],
            ['ubicacion', 'Ubicación en el Mapa', 'Cómo llegar al colegio'],
        ],
    ],
];

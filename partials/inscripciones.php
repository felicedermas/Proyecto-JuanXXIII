<?php
// ============================================================
//  partials/inscripciones.php
//  Núcleo del módulo de inscripciones, compartido por:
//    - las páginas públicas inscripcion-*.php
//      (a través de partials/inscripcion_pagina.php)
//    - el panel: gestion_inscripciones.php
//
//  Acá se define UNA sola vez:
//    - la clasificación nivel → modalidad → año
//    - los formularios y sus campos
//    - el acceso a la base (tablas, habilitado, guardado)
//
//  Agregar un formulario nuevo es definir sus campos en
//  insc_formularios() y crear la página que lo llama
//  (inscripcion-<clave>.php): el panel, la agenda, el aviso por
//  correo y la página de Inscripciones lo toman solos.
//
//  Tablas: inscripcion_formularios e inscripciones
//  (se crean solas; ver también migracion_inscripciones.sql).
// ============================================================

require_once __DIR__ . '/../conexion.php';

// ── Clasificación: nivel → modalidades y años ───────────────
//  'General' = el nivel no tiene modalidades (Inicial y Primario).
//  La clave numérica del año es el orden (sirve para ordenar).
const INSC_NIVELES = [
    'Inicial' => [
        'modalidades' => ['General'],
        'anios'       => [3 => 'Sala de 3', 4 => 'Sala de 4', 5 => 'Sala de 5'],
    ],
    'Primario' => [
        'modalidades' => ['General'],
        'anios'       => [1 => '1° grado', 2 => '2° grado', 3 => '3° grado',
                          4 => '4° grado', 5 => '5° grado', 6 => '6° grado'],
    ],
    'Secundario' => [
        'modalidades' => ['Orientada', 'Técnica', 'A definir'],
        'anios'       => [1 => '1° año', 2 => '2° año', 3 => '3° año', 4 => '4° año',
                          5 => '5° año', 6 => '6° año', 7 => '7° año'],
    ],
];

/** 7° año existe solo en la Técnica (la Orientada dura 6 años). */
function insc_anio_valido(string $nivel, string $modalidad, int $anio): bool {
    if (!isset(INSC_NIVELES[$nivel]['anios'][$anio])) return false;
    if (!in_array($modalidad, INSC_NIVELES[$nivel]['modalidades'], true)) return false;
    if ($nivel === 'Secundario' && $modalidad === 'Orientada' && $anio > 6) return false;
    return true;
}

/** "Secundario › Técnica › 3° año" (omite la modalidad 'General'). */
function insc_sector(string $nivel, string $modalidad, string $anio): string {
    $partes = [$nivel];
    if ($modalidad !== 'General') $partes[] = $modalidad;
    $partes[] = $anio;
    return implode(' › ', $partes);
}

const INSC_ESTADOS = ['Nueva', 'En revisión', 'Contactada', 'Aceptada', 'Descartada'];

const INSC_COLOR_NIVEL = [
    'Inicial'    => '#E63946',
    'Primario'   => '#1D3557',
    'Secundario' => '#457B9D',
];

// ── Campos reutilizados ─────────────────────────────────────
const INSC_OPC_VINCULO = ['Madre', 'Padre', 'Tutor/a legal', 'Otro'];
const INSC_OPC_REF     = ['Recomendación de una familia', 'Redes sociales', 'Cercanía al domicilio', 'Ex alumno/a', 'Otro'];

//  Mesas de examen (completar carrera) y becas
const INSC_OPC_CONDICION = ['Materia previa', 'Materia libre', 'Equivalencia', 'Completar carrera'];
const INSC_OPC_TURNO     = ['Turno diciembre', 'Turno febrero / marzo', 'Turno ordinario (durante el ciclo lectivo)'];
const INSC_OPC_BECA      = ['Beca solidaria', 'Beca al mérito', 'Beca hermanos', 'Beca total', 'No sé cuál corresponde'];
const INSC_OPC_INGRESOS  = ['Hasta 1 salario mínimo', 'Entre 1 y 2 salarios mínimos',
                            'Entre 2 y 3 salarios mínimos', 'Más de 3 salarios mínimos',
                            'Ingresos variables o changas', 'Sin ingresos fijos'];
const INSC_OPC_CONVIVEN  = ['2', '3', '4', '5', '6', '7 o más'];

/**
 * Definición de un campo:
 *   n => name · l => etiqueta · t => text|date|email|tel|dni|select|textarea
 *   r => obligatorio · o => opciones (select) · full => ocupa las 2 columnas
 *   ph => placeholder · max => largo máximo
 *   edad_max => (date) años hacia atrás admitidos; por defecto 25, que
 *   sirve para un alumno/a, pero no para quien vuelve a rendir materias
 *   años después de haber cursado (mesas de examen)
 */
function insc_campos_alumno(): array {
    return [
        ['n' => 'alumno_nombre',   'l' => 'Nombres del/la alumno/a', 't' => 'text', 'r' => true,  'max' => 120],
        ['n' => 'alumno_apellido', 'l' => 'Apellidos',               't' => 'text', 'r' => true,  'max' => 120],
        ['n' => 'alumno_dni',      'l' => 'DNI',                     't' => 'dni',  'r' => true,  'ph' => 'Sin puntos'],
        ['n' => 'alumno_fnac',     'l' => 'Fecha de nacimiento',     't' => 'date', 'r' => true],
        ['n' => 'alumno_nac',      'l' => 'Nacionalidad',            't' => 'text', 'r' => false, 'max' => 60],
        ['n' => 'alumno_dom',      'l' => 'Domicilio',               't' => 'text', 'r' => true,  'max' => 200, 'full' => true],
    ];
}

function insc_campos_tutor(bool $con_domicilio = false): array {
    $c = [
        ['n' => 'tutor_nombre',  'l' => 'Nombre y apellido del responsable', 't' => 'text',   'r' => true, 'max' => 160, 'full' => true],
        ['n' => 'tutor_vinculo', 'l' => 'Vínculo',                           't' => 'select', 'r' => true, 'o' => INSC_OPC_VINCULO],
        ['n' => 'tutor_dni',     'l' => 'DNI del responsable',               't' => 'dni',    'r' => true, 'ph' => 'Sin puntos'],
        ['n' => 'tutor_tel',     'l' => 'Teléfono de contacto',              't' => 'tel',    'r' => true],
        ['n' => 'tutor_email',   'l' => 'Correo electrónico',                't' => 'email',  'r' => true, 'full' => true],
    ];
    if ($con_domicilio) {
        $c[] = ['n' => 'fam_dom', 'l' => 'Domicilio del grupo familiar', 't' => 'text', 'r' => true, 'max' => 200, 'full' => true];
    }
    return $c;
}

function insc_campos_adicional(bool $con_ref = true): array {
    $c = [];
    if ($con_ref) {
        $c[] = ['n' => 'ref', 'l' => '¿Cómo nos conociste?', 't' => 'select', 'r' => false, 'o' => INSC_OPC_REF];
    }
    $c[] = ['n' => 'obs', 'l' => 'Observaciones / comentarios', 't' => 'textarea', 'r' => false, 'max' => 2000, 'full' => true,
            'ph' => 'Información que quieras compartir con nosotros…'];
    return $c;
}

/** Opciones "valor => texto" de los años de un nivel. */
function insc_opciones_anio(string $nivel): array {
    return INSC_NIVELES[$nivel]['anios'];
}

/**
 * Opciones agrupadas "Nivel|orden" => texto, con <optgroup> por nivel.
 * Es el desplegable de los formularios que no son de un nivel fijo
 * (hermanos, becas): el nivel y el año salen del mismo campo.
 */
function insc_opciones_nivel_anio(): array {
    $opc = [];
    foreach (INSC_NIVELES as $nivel => $info) {
        foreach ($info['anios'] as $orden => $texto) {
            $opc[$nivel][$nivel . '|' . $orden] = $texto;
        }
    }
    return $opc;
}

// ── Los 4 formularios ───────────────────────────────────────
//  La clave coincide con la página: inscripcion-{clave}.php
function insc_formularios(): array {
    static $defs = null;
    if ($defs !== null) return $defs;

    $pasos_base = [
        ['Completá el formulario',    'Datos del alumno/a y del responsable.'],
        ['Presentá la documentación', 'DNI, partida de nacimiento y libreta sanitaria.'],
        ['Entrevista',                'Coordinamos una entrevista con la familia.'],
    ];

    $defs = [
        'jardin' => [
            'archivo'  => 'inscripcion-jardin',
            'nombre'   => 'Jardín de Infantes',
            'titulo'   => 'Inscripción <em>Jardín de Infantes</em>',
            'bajada'   => 'Nivel Inicial · Salas de 3, 4 y 5 años',
            'icono'    => '🧸',
            'accent'   => '#E63946', 'accent_light' => '#ffb3b8',
            'nivel'    => 'Inicial',
            'grupo'    => 'nivel',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'anio', 'l' => 'Sala a la que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Inicial'), 'clasif' => 'anio'],
                ])],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'primaria' => [
            'archivo'  => 'inscripcion-primaria',
            'nombre'   => 'Nivel Primario',
            'titulo'   => 'Inscripción <em>Nivel Primario</em>',
            'bajada'   => '1° a 6° grado',
            'icono'    => '✏️',
            'accent'   => '#1D3557', 'accent_light' => '#A8DADC',
            'nivel'    => 'Primario',
            'grupo'    => 'nivel',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'anio', 'l' => 'Grado al que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Primario'), 'clasif' => 'anio'],
                ])],
                ['titulo' => 'Escuela de procedencia', 'campos' => [
                    ['n' => 'esc_anterior', 'l' => 'Institución anterior', 't' => 'text', 'r' => false, 'max' => 160, 'full' => true, 'ph' => 'Nombre del jardín o escuela'],
                    ['n' => 'esc_loc',      'l' => 'Localidad',            't' => 'text', 'r' => false, 'max' => 100],
                    ['n' => 'esc_rep',      'l' => '¿Repitió algún grado?', 't' => 'select', 'r' => false, 'o' => ['No', 'Sí']],
                ]],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'sec' => [
            'archivo'  => 'inscripcion-sec',
            'nombre'   => 'Nivel Secundario',
            'titulo'   => 'Inscripción <em>Nivel Secundario</em>',
            'bajada'   => 'Bachillerato Orientado y Educación Técnica',
            'icono'    => '🎓',
            'accent'   => '#457B9D', 'accent_light' => '#A8DADC',
            'nivel'    => 'Secundario',
            'grupo'    => 'nivel',
            'pasos'    => $pasos_base,
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => array_merge(insc_campos_alumno(), [
                    ['n' => 'modalidad', 'l' => 'Modalidad', 't' => 'select', 'r' => true, 'clasif' => 'modalidad',
                     'o' => ['Orientada' => 'Secundaria Orientada (6 años)', 'Técnica' => 'Secundaria Técnica (7 años)', 'A definir' => 'Aún no lo decidí']],
                ])],
                ['titulo' => 'Trayectoria escolar', 'campos' => [
                    ['n' => 'anio',       'l' => 'Año al que se inscribe', 't' => 'select', 'r' => true, 'o' => insc_opciones_anio('Secundario'), 'clasif' => 'anio',
                     'hint' => '7° año corresponde solo a la modalidad Técnica.'],
                    ['n' => 'sec_origen', 'l' => 'Escuela primaria / secundaria de origen', 't' => 'text', 'r' => false, 'max' => 160],
                    ['n' => 'sec_pend',   'l' => 'Materias pendientes (si las hubiera)', 't' => 'text', 'r' => false, 'max' => 300, 'full' => true, 'ph' => 'Detallar si corresponde'],
                ]],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor()],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional()],
            ],
        ],

        'hermanos' => [
            'archivo'  => 'inscripcion-hermanos',
            'nombre'   => 'Hermanos',
            'titulo'   => 'Inscripción de <em>Hermanos</em>',
            'bajada'   => 'Para familias que inscriben a dos o más hijos/as',
            'icono'    => '👨‍👩‍👧‍👦',
            'accent'   => '#6d6875', 'accent_light' => '#cfc9d4',
            'nivel'    => null,           // cada hijo/a elige su nivel
            'multiple' => true,           // un registro por hijo/a
            'grupo'    => 'otros',
            'pasos'    => [],
            'bajada_larga' => 'Para familias que inscriben a dos o más hijos/as: completás una sola vez los datos de la familia y cargás a cada hijo/a en su nivel.',
            'aviso'    => 'Completá <b>una sola vez</b> los datos del grupo familiar y agregá tantos hijos/as como necesites. Cada hijo/a queda registrado en su nivel, modalidad y año. Las familias con hermanos/as ya matriculados tienen <b>prioridad en la vacante</b>.',
            'secciones' => [
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor(true)],
                ['titulo' => 'Hijos/as a inscribir', 'hijos' => true],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional(false)],
            ],
        ],

        // Mesas de examen: lo pidió Secretaría para quienes vuelven a
        // rendir materias, sobre todo para completar la carrera. El
        // inscripto es la misma persona que deja el contacto, así que
        // no hay sección de responsable (si es menor, se agrega aparte).
        'mesas' => [
            'archivo'  => 'inscripcion-mesas',
            'nombre'   => 'Mesas de examen',
            'titulo'   => 'Inscripción a <em>mesas de examen</em>',
            'bajada'   => 'Materias previas, libres y para completar la carrera',
            'bajada_larga' => 'Para rendir materias previas o libres y para completar la carrera: cargás las materias, la condición y el turno, y Secretaría te confirma la fecha por correo.',
            'icono'    => '📋',
            'accent'   => '#0d1b2a', 'accent_light' => '#A8DADC',
            'nivel'    => 'Secundario',
            'grupo'    => 'otros',
            'ag_abre'   => 'Se abre la inscripción a mesas de examen',
            'ag_cierra' => 'Último día para inscribirse a las mesas de examen',
            'ag_curso'  => 'Inscripción a mesas de examen abierta',
            'ok_titulo' => '¡Recibimos tu inscripción a la mesa!',
            'ok_texto'  => 'Registramos las materias que vas a rendir.',
            'aviso'    => 'Si terminaste de cursar y te quedan materias para <b>completar la carrera</b>, este es el formulario. Cargá <b>todas</b> las materias que vas a rendir en el mismo envío.',
            'aviso_correo' => "\n\nLa fecha, la hora y el aula de cada mesa te las confirmamos\npor este mismo medio. El día del examen presentate con el DNI.",
            'pasos'    => [
                ['Completá el formulario',  'Materias, condición y turno en el que las rendís.'],
                ['Secretaría confirma',     'Te avisamos por correo la fecha, la hora y el aula.'],
                ['Presentate con el DNI',   'El día de la mesa, con DNI o identificación institucional.'],
            ],
            'secciones' => [
                ['titulo' => 'Datos del/la estudiante', 'campos' => [
                    ['n' => 'alumno_nombre',   'l' => 'Nombres',          't' => 'text', 'r' => true, 'max' => 120],
                    ['n' => 'alumno_apellido', 'l' => 'Apellidos',        't' => 'text', 'r' => true, 'max' => 120],
                    ['n' => 'alumno_dni',      'l' => 'DNI',              't' => 'dni',  'r' => true, 'ph' => 'Sin puntos'],
                    // edad_max alto: quien completa la carrera puede haber
                    // cursado hace muchos años (el tope de 25 no sirve acá).
                    ['n' => 'alumno_fnac',     'l' => 'Fecha de nacimiento', 't' => 'date', 'r' => true, 'edad_max' => 90],
                    ['n' => 'mesas_egreso',    'l' => 'Último año que cursaste en el colegio', 't' => 'text', 'r' => true, 'max' => 4, 'ph' => 'Ej.: 2019'],
                ]],
                ['titulo' => 'Materias a rendir', 'campos' => [
                    ['n' => 'modalidad', 'l' => 'Modalidad que cursaste', 't' => 'select', 'r' => true, 'clasif' => 'modalidad',
                     'o' => ['Orientada' => 'Secundaria Orientada', 'Técnica' => 'Secundaria Técnica']],
                    ['n' => 'anio', 'l' => 'Año al que corresponden las materias', 't' => 'select', 'r' => true,
                     'o' => insc_opciones_anio('Secundario'), 'clasif' => 'anio',
                     'hint' => '7° año corresponde solo a la modalidad Técnica.'],
                    ['n' => 'mesas_condicion', 'l' => 'Condición', 't' => 'select', 'r' => true, 'o' => INSC_OPC_CONDICION],
                    ['n' => 'mesas_turno',     'l' => 'Turno de examen', 't' => 'select', 'r' => true, 'o' => INSC_OPC_TURNO],
                    ['n' => 'mesas_materias',  'l' => 'Materias que vas a rendir', 't' => 'textarea', 'r' => true, 'max' => 1000, 'full' => true,
                     'ph' => 'Una por línea. Ej.: Matemática · Lengua y Literatura…'],
                ]],
                ['titulo' => 'Datos de contacto', 'campos' => [
                    ['n' => 'tutor_email', 'l' => 'Correo electrónico', 't' => 'email', 'r' => true, 'full' => true,
                     'hint' => 'Acá te llega la confirmación y después la fecha de la mesa.'],
                    ['n' => 'tutor_tel',   'l' => 'Teléfono de contacto', 't' => 'tel', 'r' => true],
                    ['n' => 'tutor_nombre', 'l' => 'Responsable (solo si sos menor de edad)', 't' => 'text', 'r' => false, 'max' => 160],
                ]],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional(false)],
            ],
        ],

        // Becas: la página de Becas explicaba el sistema y los
        // requisitos, pero la solicitud había que hacerla en la
        // administración. Acá se completa en línea.
        'becas' => [
            'archivo'  => 'inscripcion-becas',
            'nombre'   => 'Solicitud de beca',
            'titulo'   => 'Solicitud de <em>beca</em>',
            'bajada'   => 'Ayuda económica para sostener la trayectoria escolar',
            'bajada_larga' => 'Ayuda económica para sostener la trayectoria escolar: completás la solicitud en línea y el comité la evalúa con total confidencialidad.',
            'icono'    => '🤝',
            'accent'   => '#e09f3e', 'accent_light' => '#f3dfba',
            'nivel'    => null,           // el nivel lo elige quien solicita
            'grupo'    => 'otros',
            'ag_abre'   => 'Se abren las solicitudes de beca',
            'ag_cierra' => 'Último día para solicitar la beca',
            'ag_curso'  => 'Solicitud de beca abierta',
            'ok_titulo' => '¡Recibimos tu solicitud de beca!',
            'ok_texto'  => 'Registramos la solicitud y la situación que nos contaste.',
            'aviso'    => 'Un comité evalúa cada solicitud con <b>total confidencialidad</b>. Completar el formulario no garantiza la beca: es el primer paso para que te contactemos y coordines la entrega de la documentación.',
            'aviso_correo' => "\n\nEl comité evalúa cada solicitud de manera confidencial. Te vamos a\npedir la documentación que respalde lo declarado antes de resolver.",
            'pasos'    => [
                ['Completá la solicitud',   'Datos del alumno/a y situación del grupo familiar.'],
                ['Presentá la documentación', 'Comprobantes de ingresos y lo que se te indique.'],
                ['Evaluación y resolución', 'El comité analiza el caso y te comunicamos la respuesta.'],
            ],
            'secciones' => [
                ['titulo' => 'Datos del/la alumno/a', 'campos' => [
                    ['n' => 'alumno_nombre',   'l' => 'Nombres del/la alumno/a', 't' => 'text', 'r' => true, 'max' => 120],
                    ['n' => 'alumno_apellido', 'l' => 'Apellidos',               't' => 'text', 'r' => true, 'max' => 120],
                    ['n' => 'alumno_dni',      'l' => 'DNI',                     't' => 'dni',  'r' => true, 'ph' => 'Sin puntos'],
                    ['n' => 'alumno_fnac',     'l' => 'Fecha de nacimiento',     't' => 'date', 'r' => true],
                    ['n' => 'nivel', 'l' => 'Nivel y sala / grado / año', 't' => 'select', 'r' => true,
                     'o' => insc_opciones_nivel_anio(), 'clasif' => 'nivel', 'full' => true],
                    ['n' => 'modalidad', 'l' => 'Modalidad (solo Secundario)', 't' => 'select', 'r' => false, 'clasif' => 'modalidad',
                     'o' => ['Orientada' => 'Secundaria Orientada', 'Técnica' => 'Secundaria Técnica', 'A definir' => 'Aún no lo decidí']],
                    ['n' => 'beca_actual', 'l' => '¿Ya es alumno/a del colegio?', 't' => 'select', 'r' => true,
                     'o' => ['Sí, ya asiste', 'No, ingresa el año que viene']],
                ]],
                ['titulo' => 'Datos del responsable / tutor', 'campos' => insc_campos_tutor(true)],
                ['titulo' => 'Situación del grupo familiar', 'campos' => [
                    ['n' => 'beca_tipo',      'l' => 'Beca que solicitás', 't' => 'select', 'r' => true, 'o' => INSC_OPC_BECA],
                    ['n' => 'beca_conviven',  'l' => 'Personas que viven en el hogar', 't' => 'select', 'r' => true, 'o' => INSC_OPC_CONVIVEN],
                    ['n' => 'beca_ingresos',  'l' => 'Ingreso mensual del grupo familiar', 't' => 'select', 'r' => true, 'o' => INSC_OPC_INGRESOS],
                    ['n' => 'beca_ocupacion', 'l' => 'Ocupación de los adultos a cargo', 't' => 'text', 'r' => true, 'max' => 200, 'full' => true,
                     'ph' => 'Ej.: empleada de comercio · trabajo independiente'],
                    ['n' => 'beca_hermanos',  'l' => '¿Hay hermanos/as en el colegio?', 't' => 'select', 'r' => true,
                     'o' => ['No', 'Sí, 1', 'Sí, 2', 'Sí, 3 o más']],
                    ['n' => 'beca_ayuda',     'l' => '¿Reciben alguna ayuda social?', 't' => 'select', 'r' => true,
                     'o' => ['No', 'AUH', 'Otra ayuda o pensión', 'Prefiero contarlo en la entrevista']],
                    ['n' => 'beca_motivo',    'l' => 'Contanos la situación', 't' => 'textarea', 'r' => true, 'max' => 2000, 'full' => true,
                     'ph' => 'Por qué solicitás la beca. Lo que escribas es confidencial.'],
                ]],
                ['titulo' => 'Información adicional', 'campos' => insc_campos_adicional(false)],
            ],
        ],
    ];
    return $defs;
}

const INSC_MAX_HIJOS = 8;

/** Campos de cada hijo/a en el formulario de hermanos. */
function insc_campos_hijo(): array {
    $opc_nivel = insc_opciones_nivel_anio();   // se renderiza con <optgroup>
    return [
        ['n' => 'nombre',    'l' => 'Nombres',              't' => 'text',   'r' => true, 'max' => 120],
        ['n' => 'apellido',  'l' => 'Apellidos',            't' => 'text',   'r' => true, 'max' => 120],
        ['n' => 'dni',       'l' => 'DNI',                  't' => 'dni',    'r' => true, 'ph' => 'Sin puntos'],
        ['n' => 'fnac',      'l' => 'Fecha de nacimiento',  't' => 'date',   'r' => true],
        ['n' => 'nivel',     'l' => 'Nivel y sala / grado / año', 't' => 'select', 'r' => true, 'o' => $opc_nivel],
        ['n' => 'modalidad', 'l' => 'Modalidad (solo Secundario)', 't' => 'select', 'r' => false,
         'o' => ['Orientada' => 'Secundaria Orientada', 'Técnica' => 'Secundaria Técnica', 'A definir' => 'Aún no lo decidí']],
        ['n' => 'actual',    'l' => '¿Ya es alumno/a del colegio?', 't' => 'select', 'r' => false,
         'o' => ['No, ingresa por primera vez', 'Sí, ya asiste'], 'full' => true],
    ];
}

function insc_formulario(string $clave): ?array {
    return insc_formularios()[$clave] ?? null;
}

// ============================================================
//  BASE DE DATOS
// ============================================================

/** Crea las tablas si no existen. Devuelve false si no se pudo. */
function insc_asegurar_tablas(PDO $pdo): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS inscripcion_formularios (
            clave        VARCHAR(20)  NOT NULL PRIMARY KEY,
            habilitado   TINYINT(1)   NOT NULL DEFAULT 0,
            fecha_desde  DATE         NULL COMMENT 'Primer día en que se puede completar',
            fecha_hasta  DATE         NULL COMMENT 'Último día en que se puede completar',
            id_usuario   INT UNSIGNED NULL COMMENT 'Último usuario que lo cambió',
            actualizado  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Bases creadas antes de que existieran las fechas
        if (!$pdo->query("SHOW COLUMNS FROM inscripcion_formularios LIKE 'fecha_desde'")->fetch()) {
            $pdo->exec("ALTER TABLE inscripcion_formularios
                ADD COLUMN fecha_desde DATE NULL COMMENT 'Primer día en que se puede completar' AFTER habilitado,
                ADD COLUMN fecha_hasta DATE NULL COMMENT 'Último día en que se puede completar' AFTER fecha_desde");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS inscripciones (
            id_inscripcion  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            formulario      VARCHAR(20)  NOT NULL,
            grupo           CHAR(10)     NOT NULL COMMENT 'Código de envío; lo comparten los hermanos',
            nivel           ENUM('Inicial','Primario','Secundario') NOT NULL,
            modalidad       VARCHAR(20)  NOT NULL DEFAULT 'General',
            anio            VARCHAR(20)  NOT NULL,
            anio_orden      TINYINT UNSIGNED NOT NULL,
            alumno_nombre   VARCHAR(120) NOT NULL,
            alumno_apellido VARCHAR(120) NOT NULL,
            alumno_dni      VARCHAR(12)  NOT NULL,
            alumno_fnac     DATE         NULL,
            tutor_nombre    VARCHAR(160) NOT NULL,
            tutor_email     VARCHAR(160) NOT NULL,
            tutor_tel       VARCHAR(40)  NOT NULL,
            datos           LONGTEXT     NOT NULL COMMENT 'JSON con todo lo enviado, por sección',
            estado          ENUM('Nueva','En revisión','Contactada','Aceptada','Descartada') NOT NULL DEFAULT 'Nueva',
            notas           TEXT         NULL,
            ip              VARCHAR(45)  NOT NULL DEFAULT '',
            creado_en       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_sector (nivel, modalidad, anio_orden),
            KEY idx_estado (estado),
            KEY idx_grupo  (grupo),
            KEY idx_fecha  (creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $ins = $pdo->prepare('INSERT IGNORE INTO inscripcion_formularios (clave, habilitado) VALUES (?, 0)');
        foreach (array_keys(insc_formularios()) as $clave) {
            $ins->execute([$clave]);
        }
        $ok = true;
    } catch (Throwable $ex) {
        error_log('[juan23] inscripciones: no se pudieron crear las tablas: ' . $ex->getMessage());
        $ok = false;
    }
    return $ok;
}

/** Estado de los formularios: clave => fila (habilitado, fechas, actualizado, id_usuario). */
function insc_estados_formularios(PDO $pdo): array {
    $out = [];
    foreach ($pdo->query('SELECT * FROM inscripcion_formularios') as $f) {
        $out[$f['clave']] = $f;
    }
    return $out;
}

function insc_fila_formulario(PDO $pdo, string $clave): ?array {
    $st = $pdo->prepare('SELECT * FROM inscripcion_formularios WHERE clave = ?');
    $st->execute([$clave]);
    return $st->fetch() ?: null;
}

// ============================================================
//  PERÍODO DE INSCRIPCIÓN
// ============================================================

/** Hoy en Argentina (el php.ini del servidor puede tener otra zona horaria). */
function insc_hoy(): string {
    return (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d');
}

function insc_fecha_valida(string $v): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
}

/** 2026-11-01 → 01/11/26 (formato corto que se usa en todo el módulo) */
function insc_fecha_ar(string $ymd): string {
    [$a, $m, $d] = array_pad(explode('-', $ymd), 3, '');
    return $d . '/' . $m . '/' . substr($a, -2);
}

/** "Del 01/11/2026 al 15/12/2026", "Desde el …", "Hasta el …" o '' si no hay fechas. */
function insc_texto_periodo(?string $desde, ?string $hasta): string {
    if ($desde && $hasta) return 'Del ' . insc_fecha_ar($desde) . ' al ' . insc_fecha_ar($hasta);
    if ($desde)           return 'Desde el ' . insc_fecha_ar($desde);
    if ($hasta)           return 'Hasta el ' . insc_fecha_ar($hasta);
    return '';
}

/**
 * Estado real de un formulario combinando el interruptor y las fechas.
 * motivo: 'abierto' | 'deshabilitado' | 'proximamente' | 'finalizado'
 * Sin fila (sin base) = deshabilitado.
 */
function insc_estado(?array $fila): array {
    $desde = ($fila['fecha_desde'] ?? null) ?: null;
    $hasta = ($fila['fecha_hasta'] ?? null) ?: null;
    $hoy   = insc_hoy();

    if ((int) ($fila['habilitado'] ?? 0) !== 1) $motivo = 'deshabilitado';
    elseif ($desde && $hoy < $desde)            $motivo = 'proximamente';
    elseif ($hasta && $hoy > $hasta)            $motivo = 'finalizado';
    else                                        $motivo = 'abierto';

    return [
        'abierto' => $motivo === 'abierto',
        'motivo'  => $motivo,
        'desde'   => $desde,
        'hasta'   => $hasta,
        'periodo' => insc_texto_periodo($desde, $hasta),
    ];
}

// ============================================================
//  EVENTOS PARA LA AGENDA
// ============================================================

/** Días que dura un período, contando el primero y el último. 0 si falta una fecha. */
function insc_dias_periodo(?string $desde, ?string $hasta): int {
    if (!$desde || !$hasta) return 0;
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $desde);
    $h = DateTimeImmutable::createFromFormat('!Y-m-d', $hasta);
    if (!$d || !$h || $h < $d) return 0;
    return (int) $d->diff($h)->days + 1;
}

/** "1 día" · "45 días" · '' cuando el período no tiene las dos fechas. */
function insc_texto_duracion(?string $desde, ?string $hasta): string {
    $n = insc_dias_periodo($desde, $hasta);
    if ($n === 0) return '';
    return $n === 1 ? '1 día' : $n . ' días';
}

/**
 * Eventos de agenda generados a partir de los períodos de inscripción
 * cargados en el panel (gestion_inscripciones.php).
 *
 * NO se guardan en la tabla `agenda`: se calculan en cada visita, así la
 * agenda siempre coincide con lo que está configurado y no hay que
 * acordarse de cargar (ni de borrar) el evento a mano.
 *
 * Se publica solo lo que realmente va a pasar: el formulario tiene que
 * estar habilitado y tener al menos una de las dos fechas.
 *
 * Por cada formulario puede salir:
 *   - la apertura (en fecha_desde)
 *   - el último día (en fecha_hasta)
 *   - "inscripción abierta" en el día de hoy, mientras el período corre,
 *     para que no desaparezca de la agenda apenas pasa la apertura.
 *
 * Las filas tienen la misma forma que las de `agenda`, más:
 *   url · periodo · duracion · auto
 */
function insc_eventos_agenda(?PDO $pdo): array {
    if ($pdo === null) return [];
    try {
        $filas = insc_estados_formularios($pdo);
    } catch (Throwable $ex) {
        return [];                       // sin tablas todavía: la agenda sigue andando
    }

    $hoy = insc_hoy();
    $out = [];

    foreach (insc_formularios() as $clave => $def) {
        $fila = $filas[$clave] ?? null;
        if ($fila === null || (int) $fila['habilitado'] !== 1) continue;

        $desde = ($fila['fecha_desde'] ?? null) ?: null;
        $hasta = ($fila['fecha_hasta'] ?? null) ?: null;
        if (!$desde && !$hasta) continue;   // abierto sin período: no es una fecha de agenda

        $nombre = $def['nombre'];
        $base = [
            'id_evento'   => null,
            'etiqueta'    => $def['nivel'] ?? 'Global',
            'tipo'        => 'Inscripción',
            'hora_inicio' => null,
            'hora_fin'    => null,
            'lugar'       => null,
            'enlace'      => $def['archivo'],
            'autor'       => null,
            'url'         => $def['archivo'],
            'periodo'     => insc_texto_periodo($desde, $hasta),
            'duracion'    => insc_texto_duracion($desde, $hasta),
            'auto'        => true,
        ];

        if ($desde) {
            $out[] = $base + [
                'fecha_evento' => $desde,
                'titulo'       => $def['ag_abre'] ?? ('Se abre la inscripción · ' . $nombre),
                'descripcion'  => $hasta
                    ? 'Desde este día y hasta el ' . insc_fecha_ar($hasta)
                      . ' se puede completar el formulario en línea de ' . $nombre . '.'
                    : 'Desde este día se puede completar el formulario en línea de ' . $nombre
                      . '. La fecha de cierre todavía no está definida.',
            ];
        }

        if ($hasta && $hasta !== $desde) {
            $out[] = $base + [
                'fecha_evento' => $hasta,
                'titulo'       => $def['ag_cierra'] ?? ('Último día para inscribirse · ' . $nombre),
                'descripcion'  => 'Cierra el formulario en línea de ' . $nombre . '.'
                    . ($desde ? ' El período abrió el ' . insc_fecha_ar($desde) . '.' : ''),
            ];
        }

        // Período en curso (la apertura ya pasó y todavía no cierra)
        if ((!$desde || $desde < $hoy) && (!$hasta || $hoy < $hasta)) {
            $out[] = $base + [
                'fecha_evento' => $hoy,
                'titulo'       => $def['ag_curso'] ?? ('Inscripción abierta · ' . $nombre),
                'descripcion'  => $hasta
                    ? 'El formulario en línea se puede completar hasta el ' . insc_fecha_ar($hasta) . '.'
                    : 'El formulario en línea está abierto.',
            ];
        }
    }

    usort($out, fn($a, $b) => [$a['fecha_evento'], $a['titulo']] <=> [$b['fecha_evento'], $b['titulo']]);
    return $out;
}

// ============================================================
//  VALIDACIÓN
// ============================================================

/** Normaliza y valida un valor según su campo. Devuelve [valor, error|null]. */
function insc_validar_campo(array $c, $bruto): array {
    $v = is_string($bruto) ? trim(preg_replace('/\s+/u', ' ', $bruto) ?? '') : '';
    if ($c['t'] === 'textarea' && is_string($bruto)) {
        $v = trim(str_replace("\r\n", "\n", $bruto));
    }

    if ($v === '') {
        return ['', !empty($c['r']) ? 'Este campo es obligatorio.' : null];
    }

    $max = $c['max'] ?? 160;
    if (mb_strlen($v) > $max) {
        return [$v, "Máximo $max caracteres."];
    }

    switch ($c['t']) {
        case 'dni':
            $v = preg_replace('/[.\s-]/', '', $v);
            if (!preg_match('/^[0-9]{7,8}$/', $v)) return [$v, 'Ingresá el DNI sin puntos (7 u 8 números).'];
            break;
        case 'email':
            if (!filter_var($v, FILTER_VALIDATE_EMAIL)) return [$v, 'El correo no parece válido.'];
            break;
        case 'tel':
            if (!preg_match('/^[0-9+\s()\-]{6,25}$/', $v)) return [$v, 'Usá solo números, espacios, guiones o +.'];
            break;
        case 'date':
            $d = DateTime::createFromFormat('!Y-m-d', $v);
            if (!$d || $d->format('Y-m-d') !== $v) return [$v, 'Fecha inválida.'];
            $anios = (int) $d->diff(new DateTime('today'))->format('%r%y');
            if ($d > new DateTime('today') || $anios > (int) ($c['edad_max'] ?? 25)) {
                return [$v, 'Revisá la fecha de nacimiento.'];
            }
            break;
        case 'select':
            if (!in_array($v, insc_valores_opciones($c['o']), true)) return ['', 'Elegí una opción de la lista.'];
            break;
    }
    return [$v, null];
}

/** Lista plana de valores válidos de un select (admite listas, mapas y grupos). */
function insc_valores_opciones(array $opciones): array {
    $vals = [];
    $es_lista = array_is_list($opciones);
    foreach ($opciones as $k => $o) {
        if (is_array($o)) {                         // <optgroup>
            foreach ($o as $kk => $oo) $vals[] = (string) $kk;
        } else {
            $vals[] = $es_lista ? (string) $o : (string) $k;
        }
    }
    return $vals;
}

/** Texto visible de una opción elegida. */
function insc_texto_opcion(array $opciones, string $valor): string {
    $es_lista = array_is_list($opciones);
    foreach ($opciones as $k => $o) {
        if (is_array($o)) {
            if (isset($o[$valor])) return $k . ' · ' . $o[$valor];
        } elseif (!$es_lista && (string) $k === $valor) {
            return $o;
        }
    }
    return $valor;
}

/**
 * Valida el POST completo de un formulario.
 * Devuelve ['valores' => [...], 'errores' => [name => msg], 'alumnos' => [filas listas para guardar]]
 */
function insc_procesar(string $clave, array $post): array {
    $def     = insc_formulario($clave);
    $valores = [];
    $errores = [];
    $datos_comunes = [];   // secciones compartidas (tutor, adicional...) para el JSON
    $datos_alumno  = [];   // secciones propias del alumno (form. individuales)

    $modalidad = 'General';
    $anio_ord  = 0;
    $nivel_sel = '';       // formularios que preguntan el nivel (becas)

    foreach ($def['secciones'] as $sec) {
        if (!empty($sec['hijos'])) continue;
        $filas = [];
        foreach ($sec['campos'] as $c) {
            [$v, $err] = insc_validar_campo($c, $post[$c['n']] ?? '');
            $valores[$c['n']] = $v;
            if ($err) $errores[$c['n']] = $err;

            if (($c['clasif'] ?? '') === 'anio')      $anio_ord  = (int) $v;
            if (($c['clasif'] ?? '') === 'modalidad') $modalidad = $v;
            if (($c['clasif'] ?? '') === 'nivel') {
                // Un solo campo trae las dos cosas: "Secundario|3"
                [$nivel_sel, $ord_sel] = array_pad(explode('|', $v), 2, '');
                $anio_ord = (int) $ord_sel;
            }

            if ($v !== '') {
                $filas[] = [$c['l'], $c['t'] === 'select' ? insc_texto_opcion($c['o'], $v) : insc_formatear($c, $v)];
            }
        }
        $es_de_alumno = str_starts_with($sec['titulo'], 'Datos del/la') || in_array($sec['titulo'], ['Escuela de procedencia', 'Trayectoria escolar'], true);
        if ($es_de_alumno) $datos_alumno[]  = ['seccion' => $sec['titulo'], 'campos' => $filas];
        else               $datos_comunes[] = ['seccion' => $sec['titulo'], 'campos' => $filas];
    }

    // Declaración jurada
    $valores['acepto'] = !empty($post['acepto']);
    if (!$valores['acepto']) {
        $errores['acepto'] = 'Necesitamos que confirmes la declaración para enviar.';
    }

    $tutor = [
        'tutor_nombre' => $valores['tutor_nombre'] ?? '',
        'tutor_email'  => $valores['tutor_email'] ?? '',
        'tutor_tel'    => $valores['tutor_tel'] ?? '',
    ];
    // Mesas de examen: el inscripto es su propio contacto (el campo de
    // responsable solo se completa si es menor de edad).
    if ($tutor['tutor_nombre'] === '') {
        $tutor['tutor_nombre'] = trim(($valores['alumno_nombre'] ?? '') . ' ' . ($valores['alumno_apellido'] ?? ''));
    }

    $alumnos = [];

    if (empty($def['multiple'])) {
        // ── Formulario individual (un solo alumno/a) ──
        //  El nivel puede venir fijo del formulario (jardín, primaria,
        //  secundaria, mesas) o elegirse en un campo (becas).
        $nivel      = $def['nivel'] ?? $nivel_sel;
        $campo_anio = ($def['nivel'] ?? null) === null ? 'nivel' : 'anio';

        if (!isset(INSC_NIVELES[$nivel])) {
            $errores[$campo_anio] = $errores[$campo_anio] ?? 'Elegí una opción de la lista.';
            $nivel = '';
        } elseif ($nivel !== 'Secundario') {
            $modalidad = 'General';          // solo el Secundario tiene modalidades
        } elseif ($modalidad === '') {
            $errores['modalidad'] = 'Para Secundario, elegí la modalidad.';
        }

        if ($nivel !== '' && !isset($errores[$campo_anio]) && !isset($errores['modalidad'])
            && $anio_ord > 0 && !insc_anio_valido($nivel, $modalidad, $anio_ord)) {
            $errores[$campo_anio] = 'La modalidad Orientada tiene 6 años: elegí de 1° a 6°.';
        }
        if ($nivel === '') {
            return ['valores' => $valores, 'errores' => $errores, 'alumnos' => []];
        }
        $alumnos[] = $tutor + [
            'nivel'           => $nivel,
            'modalidad'       => $modalidad,
            'anio'            => INSC_NIVELES[$nivel]['anios'][$anio_ord] ?? '',
            'anio_orden'      => $anio_ord,
            'alumno_nombre'   => $valores['alumno_nombre'] ?? '',
            'alumno_apellido' => $valores['alumno_apellido'] ?? '',
            'alumno_dni'      => $valores['alumno_dni'] ?? '',
            'alumno_fnac'     => ($valores['alumno_fnac'] ?? '') ?: null,
            'datos'           => array_merge($datos_alumno, $datos_comunes),
        ];
    } else {
        // ── Hermanos: un registro por hijo/a ──
        $hijos_post = is_array($post['hijos'] ?? null) ? array_values($post['hijos']) : [];
        $hijos_post = array_slice($hijos_post, 0, INSC_MAX_HIJOS);
        // Se descartan las filas totalmente vacías (el usuario agregó y no completó)
        $hijos_post = array_values(array_filter($hijos_post, function ($h) {
            return is_array($h) && implode('', array_map(fn($x) => is_string($x) ? trim($x) : '', $h)) !== '';
        }));

        $valores['hijos'] = [];
        if (count($hijos_post) < 2) {
            $errores['hijos'] = 'Cargá al menos dos hijos/as. Si inscribís a uno solo, usá el formulario de su nivel.';
        }
        $dnis = [];
        foreach ($hijos_post as $i => $h) {
            $vh = [];
            $filas = [];
            foreach (insc_campos_hijo() as $c) {
                [$v, $err] = insc_validar_campo($c, $h[$c['n']] ?? '');
                $vh[$c['n']] = $v;
                if ($err) $errores["hijos.$i.{$c['n']}"] = $err;
                if ($v !== '') {
                    $filas[] = [$c['l'], $c['t'] === 'select' ? insc_texto_opcion($c['o'], $v) : insc_formatear($c, $v)];
                }
            }
            $valores['hijos'][$i] = $vh;

            [$nivel, $ord] = array_pad(explode('|', $vh['nivel']), 2, '0');
            $ord = (int) $ord;
            $mod = 'General';
            if ($nivel === 'Secundario') {
                $mod = $vh['modalidad'];
                if ($mod === '') {
                    $errores["hijos.$i.modalidad"] = 'Para Secundario, elegí la modalidad.';
                } elseif ($ord > 0 && !insc_anio_valido($nivel, $mod, $ord)) {
                    $errores["hijos.$i.nivel"] = 'La modalidad Orientada tiene 6 años: elegí de 1° a 6°.';
                }
            }
            if ($vh['dni'] !== '' && in_array($vh['dni'], $dnis, true)) {
                $errores["hijos.$i.dni"] = 'Este DNI ya está cargado en otro hijo/a.';
            }
            $dnis[] = $vh['dni'];

            if (isset(INSC_NIVELES[$nivel])) {
                $alumnos[] = $tutor + [
                    'nivel'           => $nivel,
                    'modalidad'       => $mod,
                    'anio'            => INSC_NIVELES[$nivel]['anios'][$ord] ?? '',
                    'anio_orden'      => $ord,
                    'alumno_nombre'   => $vh['nombre'],
                    'alumno_apellido' => $vh['apellido'],
                    'alumno_dni'      => $vh['dni'],
                    'alumno_fnac'     => $vh['fnac'] ?: null,
                    'datos'           => array_merge([['seccion' => 'Datos del/la alumno/a', 'campos' => $filas]], $datos_comunes),
                ];
            }
        }
        if (!$valores['hijos']) {
            $valores['hijos'] = [[], []];   // para volver a mostrar dos bloques vacíos
        }
    }

    return ['valores' => $valores, 'errores' => $errores, 'alumnos' => $alumnos];
}

/** Formato legible para el panel (fechas en dd/mm/aaaa). */
function insc_formatear(array $c, string $v): string {
    if ($c['t'] === 'date' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
        return "$m[3]/$m[2]/$m[1]";
    }
    return $v;
}

/** Guarda los alumnos de un envío. Devuelve el código de grupo. */
function insc_guardar(PDO $pdo, string $clave, array $alumnos, string $ip): string {
    $grupo = strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
    $st = $pdo->prepare(
        'INSERT INTO inscripciones
           (formulario, grupo, nivel, modalidad, anio, anio_orden,
            alumno_nombre, alumno_apellido, alumno_dni, alumno_fnac,
            tutor_nombre, tutor_email, tutor_tel, datos, ip)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($alumnos as $a) {
            $st->execute([
                $clave, $grupo, $a['nivel'], $a['modalidad'], $a['anio'], $a['anio_orden'],
                $a['alumno_nombre'], $a['alumno_apellido'], $a['alumno_dni'], $a['alumno_fnac'],
                $a['tutor_nombre'], $a['tutor_email'], $a['tutor_tel'],
                json_encode($a['datos'], JSON_UNESCAPED_UNICODE), $ip,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
    return $grupo;
}

/** ¿Demasiados envíos desde la misma IP en poco tiempo? (anti-spam simple) */
function insc_limite_superado(PDO $pdo, string $ip): bool {
    $st = $pdo->prepare(
        'SELECT COUNT(DISTINCT grupo) FROM inscripciones
          WHERE ip = ? AND creado_en > (NOW() - INTERVAL 15 MINUTE)'
    );
    $st->execute([$ip]);
    return (int) $st->fetchColumn() >= 6;
}

// ============================================================
//  AVISO POR CORREO
// ------------------------------------------------------------
//  Lo que pidió Secretaría: que quien se inscribe reciba una
//  confirmación automática, y que la casilla del colegio reciba
//  el aviso de que entró algo nuevo (hasta ahora la inscripción
//  quedaba registrada y solo se veía entrando al panel).
//
//  Se llama después de guardar. Si el correo falla, la
//  inscripción YA está guardada: solo queda el registro en el log.
// ============================================================

require_once __DIR__ . '/correo.php';

/** Una línea por alumno/a: "Apellido, Nombre — Secundario › Técnica › 3° año". */
function insc_resumen_alumnos(array $alumnos): string {
    $lineas = [];
    foreach ($alumnos as $a) {
        $lineas[] = '- ' . $a['alumno_apellido'] . ', ' . $a['alumno_nombre']
                  . ' — ' . insc_sector($a['nivel'], $a['modalidad'], $a['anio'])
                  . ' (DNI ' . $a['alumno_dni'] . ')';
    }
    return implode("\n", $lineas);
}

/**
 * Confirmación a quien completó el formulario + aviso a la casilla
 * del colegio. Devuelve true si salió la confirmación a la familia.
 */
function insc_avisar_envio(array $def, array $alumnos, string $grupo): bool {
    if (!$alumnos) return false;

    $colegio  = cfg('nombre_colegio');
    $formu    = $def['nombre'];
    $contacto = (string) ($alumnos[0]['tutor_email'] ?? '');
    $resumen  = insc_resumen_alumnos($alumnos);
    $cuantos  = count($alumnos);

    // ── 1. Confirmación a quien se inscribió ──
    $cuerpo = "¡Hola, {$alumnos[0]['tutor_nombre']}!\n\n"
        . "Recibimos la preinscripción a $formu en $colegio.\n\n"
        . ($cuantos > 1 ? "Quedaron registrados:\n" : "Quedó registrado:\n")
        . $resumen . "\n\n"
        . "Código de envío: $grupo\n"
        . "Guardalo: es el número con el que seguimos el trámite.\n\n"
        . "¿Cómo sigue? La Secretaría se va a comunicar por teléfono o por\n"
        . "correo para coordinar la entrevista y la presentación de la\n"
        . "documentación. Este mensaje confirma que el formulario llegó, no\n"
        . "confirma la vacante.\n"
        . ($def['aviso_correo'] ?? '')
        . correo_pie();

    $ok = correo_enviar(
        $contacto,
        'Recibimos tu preinscripción · ' . $formu,
        $cuerpo,
        correo_casilla_interna()
    );

    // ── 2. Aviso interno (Secretaría / Admisiones) ──
    $interna = correo_casilla_interna();
    if ($interna !== '') {
        $cuerpo_int = "Entró una inscripción nueva por el sitio.\n\n"
            . "Formulario: $formu\n"
            . "Código de envío: $grupo\n"
            . "Fecha: " . date('d/m/Y H:i') . "\n\n"
            . ($cuantos > 1 ? "Alumnos/as ($cuantos):\n" : "Alumno/a:\n")
            . $resumen . "\n\n"
            . "Responsable: {$alumnos[0]['tutor_nombre']}\n"
            . "Correo: $contacto\n"
            . "Teléfono: {$alumnos[0]['tutor_tel']}\n\n"
            . "Se ve completa en el panel → Inscripciones."
            . correo_pie();
        correo_enviar($interna, 'Inscripción nueva · ' . $formu, $cuerpo_int, $contacto);
    }

    return $ok;
}

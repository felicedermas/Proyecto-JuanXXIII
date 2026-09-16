-- ============================================================
--  migracion_configuracion.sql
--  Colegio Parroquial Juan XXIII
--  Tabla de configuración del sitio: toda la información de
--  contacto pasa a ser editable desde el panel.
--  Ejecutar UNA vez sobre la base colegio_juan_xxiii.
-- ============================================================

CREATE TABLE IF NOT EXISTS configuracion (
  clave       VARCHAR(60)  NOT NULL PRIMARY KEY,
  valor       TEXT         NOT NULL,
  etiqueta    VARCHAR(140) NOT NULL COMMENT 'Nombre visible en el panel',
  ayuda       VARCHAR(220) NOT NULL DEFAULT '' COMMENT 'Texto de ayuda bajo el campo',
  tipo        ENUM('texto','textarea','email','tel','url') NOT NULL DEFAULT 'texto',
  grupo       VARCHAR(40)  NOT NULL DEFAULT 'General',
  orden       INT          NOT NULL DEFAULT 0,
  actualizado TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Datos del colegio editables desde el panel (contacto, redes, horarios)';

-- ── Valores iniciales ───────────────────────────────────────
-- INSERT IGNORE: si volvés a correr el script no pisa lo que ya editaste.

INSERT IGNORE INTO configuracion (clave, valor, etiqueta, ayuda, tipo, grupo, orden) VALUES
-- Identidad
('nombre_colegio',   'Colegio Parroquial Juan XXIII', 'Nombre del colegio',      'Aparece en el header, el pie y el título de todas las páginas.', 'texto',    'Identidad', 10),
('lema',             'Educando desde 1962',           'Lema',                    'Frase corta bajo el nombre, en el header.',                      'texto',    'Identidad', 20),
('pie_descripcion',  'Formando profesionales desde 1962.', 'Frase del pie',      'Texto breve junto al logo en el pie de página.',                 'texto',    'Identidad', 30),
('anio_fundacion',   '1962',                          'Año de fundación',        'Se usa en textos automáticos.',                                  'texto',    'Identidad', 40),

-- Contacto
('telefono',         '(011) 1234-5678',               'Teléfono (como se ve)',   'Formato legible. Ej.: (011) 4658-1234',                          'texto',    'Contacto', 10),
('telefono_link',    '+541112345678',                 'Teléfono (para llamar)',  'Mismo número sin espacios ni guiones, con código de país. Ej.: +541146581234', 'tel', 'Contacto', 20),
('telefono_alt',     '',                              'Teléfono alternativo',    'Opcional. Dejalo vacío si no hay segundo número.',               'texto',    'Contacto', 30),
('telefono_alt_link','',                              'Teléfono alt. (para llamar)', 'Opcional.',                                                  'tel',      'Contacto', 40),
('whatsapp',         '',                              'WhatsApp (como se ve)',   'Opcional. Ej.: 11 2345-6789',                                    'texto',    'Contacto', 50),
('whatsapp_link',    '',                              'WhatsApp (número)',       'Solo números con código de país, sin + ni espacios. Ej.: 5491123456789', 'texto', 'Contacto', 60),
('email',            'info@juanxxiii.edu.ar',         'Email general',           'Se muestra en el pie y en la página de contacto.',               'email',    'Contacto', 70),
('email_admisiones', 'admisiones@juanxxiii.edu.ar',   'Email de admisiones',     'Para consultas de inscripción.',                                 'email',    'Contacto', 80),

-- Ubicación
('direccion',        'Av. Rivadavia 1234',            'Dirección',               'Calle y número.',                                                'texto',    'Ubicación', 10),
('localidad',        'Haedo, Buenos Aires',           'Localidad',               'Localidad y provincia.',                                         'texto',    'Ubicación', 20),
('codigo_postal',    'B1706',                         'Código postal',           '',                                                               'texto',    'Ubicación', 30),
('mapa_embed',       '',                              'Mapa incrustado',         'Pegá acá la URL del iframe de Google Maps (Compartir → Insertar un mapa → copiá solo el src).', 'url', 'Ubicación', 40),
('mapa_link',        '',                              'Link "Cómo llegar"',      'URL de Google Maps para abrir en una pestaña nueva.',            'url',      'Ubicación', 50),

-- Horarios
('horario_atencion', 'Lunes a viernes de 8:00 a 16:00 h', 'Horario de secretaría', 'Se muestra en contacto y en el pie.',                          'texto',    'Horarios', 10),
('horario_admision', 'Lunes a viernes de 9:00 a 13:00 h', 'Horario de admisiones', 'Para las páginas de inscripción.',                             'texto',    'Horarios', 20),

-- Redes y plataformas
('facebook',         '',                              'Facebook',                'URL completa del perfil. Vacío = no se muestra el ícono.',       'url',      'Redes', 10),
('instagram',        '',                              'Instagram',               'URL completa del perfil. Vacío = no se muestra el ícono.',       'url',      'Redes', 20),
('youtube',          '',                              'YouTube',                 'URL completa del canal. Vacío = no se muestra el ícono.',        'url',      'Redes', 30),
('url_xhendra',      '#',                             'Acceso a Xhendra',        'URL del campus/plataforma a la que lleva el botón de Xhendra.',  'url',      'Redes', 40);

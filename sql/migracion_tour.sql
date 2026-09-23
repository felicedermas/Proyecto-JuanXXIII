-- ============================================================
--  migracion_tour.sql
--  Colegio Parroquial Juan XXIII — Recorrido Virtual 360°
--  Ejecutar una sola vez sobre la base colegio_juan_xxiii
-- ============================================================

-- Cada foto 360 del colegio es una "escena"
CREATE TABLE IF NOT EXISTS tour_escenas (
  id_escena     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100)  NOT NULL,                  -- "Patio central"
  zona          VARCHAR(80)   NOT NULL DEFAULT 'General',-- agrupa el menú: "Nivel Primario", etc.
  url_imagen    VARCHAR(255)  NOT NULL,                  -- img/tour/xxxx.jpg
  yaw_inicial   DECIMAL(6,1)  NOT NULL DEFAULT 0,        -- hacia dónde mira al entrar
  pitch_inicial DECIMAL(6,1)  NOT NULL DEFAULT 0,
  orden         INT           NOT NULL DEFAULT 0,        -- orden dentro de la zona
  activa        TINYINT(1)    NOT NULL DEFAULT 1,        -- 0 = oculta en la web pública
  creado_en     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Puntos clickeables dentro de cada escena:
--   tipo 'nav'  → flecha que lleva a otra escena (id_destino)
--   tipo 'info' → globo informativo con texto
CREATE TABLE IF NOT EXISTS tour_hotspots (
  id_hotspot INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_escena  INT UNSIGNED  NOT NULL,                 -- escena donde está el punto
  tipo       ENUM('nav','info') NOT NULL DEFAULT 'nav',
  id_destino INT UNSIGNED  NULL,                     -- escena a la que lleva (solo tipo nav)
  yaw        DECIMAL(6,1)  NOT NULL,                 -- posición horizontal (-180 a 180)
  pitch      DECIMAL(6,1)  NOT NULL,                 -- posición vertical (-90 a 90)
  texto      VARCHAR(180)  NOT NULL DEFAULT '',      -- tooltip: "Ir al patio", "Secretaría..."
  CONSTRAINT fk_hs_escena  FOREIGN KEY (id_escena)  REFERENCES tour_escenas(id_escena)  ON DELETE CASCADE,
  CONSTRAINT fk_hs_destino FOREIGN KEY (id_destino) REFERENCES tour_escenas(id_escena)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

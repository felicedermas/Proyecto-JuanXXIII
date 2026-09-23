<?php
// ============================================================
//  Configuración de la sincronización con Instagram
//  Colegio Parroquial Juan XXIII
// ============================================================
//
//  Cómo funciona:
//  - Mientras IG_SIMULAR = true, el script NO llama a Instagram:
//    usa el archivo posts_simulados.json para que puedas probar
//    todo el flujo localmente sin token ni cuenta Business.
//  - Cuando tengas la cuenta y el token, poné IG_SIMULAR = false
//    y completá IG_USER_ID + IG_ACCESS_TOKEN. No hay que tocar
//    ninguna otra línea de código.
// ============================================================

// ── Conexión a la base: fuente única ──
require_once __DIR__ . '/conexion.php';

// ── Modo de funcionamiento ──
// true  = lee posts_simulados.json (pruebas locales)
// false = consulta la Instagram Graph API real
const IG_SIMULAR = true;

// ── Credenciales de la API (solo se usan si IG_SIMULAR = false) ──
// Las obtenés siguiendo GUIA_INSTAGRAM.md
const IG_USER_ID      = 'PEGAR_AQUI_TU_INSTAGRAM_USER_ID';
const IG_ACCESS_TOKEN = 'PEGAR_AQUI_TU_LONG_LIVED_TOKEN';

// ── Comportamiento de la importación ──
// id_usuario que figurará como autor (el "Instagram Colegio" creado en el SQL).
// Verificá el número con:
//   SELECT id_usuario FROM usuarios WHERE email='instagram@juanxxiii.edu.ar';
const IG_AUTHOR_USER_ID = 5;

// Cuántos posts traer por sincronización.
const IG_LIMITE_POSTS = 12;

// Etiqueta por defecto para los posts de Instagram.
// Debe ser uno de: Inicial, Primario, Técnica, Orientada, Global
const IG_ETIQUETA_DEFAULT = 'Global';

// Carpeta donde se descargan las imágenes (relativa a la raíz del sitio).
// Importante: las URLs de imagen de Instagram caducan, por eso conviene
// guardar una copia local en lugar de enlazar directo a Instagram.
const IG_CARPETA_IMAGENES = 'img/novedades/instagram';

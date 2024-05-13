<?php
/*
Plugin Name: Invalidar Cache CloudFront TGR
Plugin URI:
Description: Plugin para invalidar cache de CloudFront .
Author: DeepSource
Author URI:
Version: 2.0
*/

if (!defined('ABSPATH')) {
    exit; // Seguridad: Abortar si se llama directamente
}


// Cargar la SDK de AWS para PHP cuando el plugin se inicialice
function cargar_sdk_aws() {
    require_once __DIR__ . '/vendor/aws-autoloader.php'; // Ajusta la ruta si es necesario
}

add_action('plugins_loaded', 'cargar_sdk_aws');

// Registro de menús y páginas de configuración
require_once __DIR__ . '/includes/admin-menu.php';

// Procesamiento de la invalidación de caché
require_once __DIR__ . '/includes/process-invalidation.php';

// Utilidades
require_once __DIR__ . '/includes/utilities.php';

// Agregar estilos personalizados para la página de administración
function agregar_estilo_admin() {
    wp_enqueue_style('mi-estilo-admin', plugin_dir_url(__FILE__) . 'admin-style.css');
}
add_action('admin_enqueue_scripts', 'agregar_estilo_admin');

// Permitir que los editores puedan editar y ver el plugin como administrador
$editor_role = get_role('editor');
if ($editor_role) {
    $editor_role->add_cap('manage_options');
}



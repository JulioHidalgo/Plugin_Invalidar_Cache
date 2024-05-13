<?php
if (!defined('ABSPATH')) exit;

// Obtener la URL base del plugin
$plugin_url = plugin_dir_url(__FILE__);

// Ruta de la imagen
$ruta_imagen = $plugin_url . 'web.png';

function addMenu() {
    add_menu_page("Invalidar Cache", "Invalidar Cache", "manage_options", "invalidar-cache", "interfaz", 'dashicons-star-filled');
    add_submenu_page("invalidar-cache", "Configuración AWS", "Configuración AWS", "manage_options", "aws-settings", "plugin_settings_page");
}

add_action("admin_menu", "addMenu", "agregar_submenu_consultar_estado");

function agregar_submenu_consultar_estado() {
    add_submenu_page(
        'options-general.php',
        'Consultar Estado de Invalidación', 
        'Consultar Estado',
        'publish_posts',
        'consultar_estado_invalidation',
        'mostrar_pagina_consultar_estado'
    );
}


function plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>Configuración de Invalidar Cache CloudFront</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields("aws_settings_group");
            do_settings_sections("aws-settings");
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function display_aws_settings() {
    add_settings_section("aws_settings_section", "Accesos y Distribución para AWS", null, "aws-settings");
    
    add_settings_field("aws_access_key", "AWS Access Key", "display_aws_access_key_element", "aws-settings", "aws_settings_section");
    add_settings_field("aws_secret_key", "AWS Secret Key", "display_aws_secret_key_element", "aws-settings", "aws_settings_section");

    add_settings_field("aws_distribution_id_1", "AWS CloudFront Distribution ID 1", "display_aws_distribution_id_1_element", "aws-settings", "aws_settings_section");
    add_settings_field("aws_distribution_id_2", "AWS CloudFront Distribution ID 2", "display_aws_distribution_id_2_element", "aws-settings", "aws_settings_section");
    
    register_setting("aws_settings_group", "aws_access_key");
    register_setting("aws_settings_group", "aws_secret_key");

    register_setting("aws_settings_group", "aws_distribution_id_1");
    register_setting("aws_settings_group", "aws_distribution_id_2");
}

function display_aws_distribution_id_1_element() {
    ?>
    <input type="text" name="aws_distribution_id_1" id="aws_distribution_id_1" value="<?php echo get_option('aws_distribution_id_1'); ?>" />
    <?php
}

function display_aws_distribution_id_2_element() {
    ?>
    <input type="text" name="aws_distribution_id_2" id="aws_distribution_id_2" value="<?php echo get_option('aws_distribution_id_2'); ?>" />
    <?php
}


function display_aws_access_key_element() {
    ?>
    <input type="text" name="aws_access_key" id="aws_access_key" value="<?php echo get_option('aws_access_key'); ?>" />
    <?php
}

function display_aws_secret_key_element() {
    ?>
    <input type="password" name="aws_secret_key" id="aws_secret_key" value="<?php echo get_option('aws_secret_key'); ?>" />
    <?php
}

function display_aws_distribution_id_element() {
    ?>
    <input type="text" name="aws_distribution_id" id="aws_distribution_id" value="<?php echo get_option('aws_distribution_id'); ?>" />
    <?php
}

add_action("admin_init", "display_aws_settings");

function interfaz() {
    ?>
    <div class="wrap">
        <h2 class="titulo">Invalidar caché del sitio</h2>
        <p class="parrafo">Este plugin permite invalidar la caché del sitio y que el contenido modificado quede visible para todas las personas.</p>
        <form  class="principal" method='POST' action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="invalidate_cache">
            <label for="paths" class="paths">Rutas de páginas a invalidar</label>
            <br><br>
            <input type="text" id="input_consultar" class="input-inv" name="paths" oninput="document.getElementById('boton_consultar').disabled = !this.value.trim(); if(this.value.trim()) {this.setAttribute('class', 'input-inv')} else {this.setAttribute('class', 'input-inv')}" required/>
            <p>Ingresa las rutas de las páginas separadas por comas, sin espacios de por medio. Ejemplo: <p class="txt-bold p-lh">/personas-mayores,/solicitudes-en-linea,/contacto</p></p>             
            <br>
            <input type="submit" id="boton_consultar" name="invalidar" value="Invalidar caché de páginas" class="invalida button button-primary " disabled/>
        </form>
        <div class="wrap principal2">
            <h4 class="bold">Modo de uso:</h4>
            <ul>
<li><strong class="bold">1. Invalidar contenido de una o más páginas:</strong><p>Si modificaste contenido en páginas específicas como por ejemplo Contribuciones, Solicitudes de Oficina Virtual o Convenios de Pago, debes ingresar en el campo de Rutas de páginas a invalidar separadas por coma sin espacios de por medio:</p>
<p><span class="spanverde">/contribuciones<span class="spannegro">,</span></span><span class="spanrojo">/oficina-virtual-tgr<span class="spannegro">,</span></span><span class="spanazul">/convenios-de-pago</span></p></li>
<br>
<li><strong class="bold">2. Invalidar contenido de una página contenida dentro de otra página:</strong><p>Por ejemplo, si modificaste la página de Centro de Documentación, que está contenida dentro de Portal Municipal, debes ingresar en el campo de Rutas de páginas a invalidar la ruta completa:</p></li>
<p class="txt-bold"><span class="spanverde">/portal-municipal/centro-de-documentacion</span></p>
<br>
<li><strong class="bold">3. Invalidar sólo el sitio principal de inicio o home:</strong><p>Si modificaste la página principal (home), se invalida con sólo un guion, ejemplo:</p></li>
<p class="txt-bold"><span class="spanverde">/</span></p>
<br>
<li><strong class="bold">4. Invalidar contenido transversal en el sitio:</strong><p>Si modificaste contenido que es transversal en el sitio, es decir, que se ve 2 o más páginas como el header, footer, alertas transversales, accesibilidad, entre otros, debes invalidar el sitio en su totalidad.</p>
<p>Por ejemplo, si agregaste una nueva opción en el menú del sitio y además modificaste una información en el footer, debes agregar en el campo de Rutas de páginas a invalidar:</p>
<p><span class="spanverde">/*</span></p></li>
<p><span>Utiliza esta invalidación transversal con discreción y sólo para los casos en que se requiera.</span></p>
<br>
<li><strong class="bold">¿Dónde encuentro la ruta de la página en la que estoy?</strong><p></p></li>
<p><li class="disc">Está en la parte superior del navegador que estás usando, por ejemplo:</li></p>
</ul>
<img src="https://www.dev.tegere.info/wp-content/uploads/2024/04/web.png" title="ejemplo de imagen" alt="ejemplo de imagen" style=" width: 100%; ">
        </div>
        <?php
        // Mostrar mensajes de feedback
        if (get_transient('invalidate_cache_feedback')) {
            echo display_transient_messages('invalidate_cache_feedback');
            delete_transient('invalidate_cache_feedback');
        }
    ?>
    </div>
    <?php
}

function mostrar_pagina_consultar_estado() {
    ?>
    <div class="wrap">
        <h2>Consultar Estado de Invalidación de Distribuciones</h2>

        <!-- Formulario para consultar el estado de invalidación -->
        <form method="post">
            <?php wp_nonce_field('consultar_estado_invalidation_nonce', 'consultar_estado_invalidation_nonce'); ?>
            <input type="submit" name="consultar_estado_invalidation" value="Consultar Estado de Invalidación" class="button button-primary"/>
        </form>

        <?php
            // Mostrar el resultado de la consulta de estado de invalidación
            if (isset($_POST['consultar_estado_invalidation']) && check_admin_referer('consultar_estado_invalidation_nonce', 'consultar_estado_invalidation_nonce')) {
                consultar_estado_invalidation();
            }
        ?>
    </div>
    <?php
}


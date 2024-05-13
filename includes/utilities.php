<?php
if (!defined('ABSPATH')) exit;

/**
 * Sanitiza las entradas de texto.
 *
 * @param string $input El valor de entrada a sanitizar.
 * @return string El valor sanitizado.
 */
function sanitize_text_field_custom($input) {
    return sanitize_text_field($input);
}

/**
 * Valida las claves de AWS asegurándose de que no estén vacías y sean alfanuméricas.
 *
 * @param string $key La clave de AWS a validar.
 * @return bool Verdadero si la clave es válida, falso si no.
 */
function validate_aws_key($key) {
    // La clave no debe estar vacía y debe ser alfanumérica.
    return !empty($key) && ctype_alnum($key);
}

/**
 * Sanitiza y valida un URL.
 *
 * @param string $url El URL a validar y sanitizar.
 * @return string|false El URL sanitizado si es válido, o falso si no lo es.
 */
function sanitize_validate_url($url) {
    $sanitized_url = esc_url_raw($url);
    return filter_var($sanitized_url, FILTER_VALIDATE_URL) ? $sanitized_url : false;
}

/**
 * Muestra mensajes de error o éxito almacenados en transitorios.
 *
 * @param string $transient_name El nombre del transitorio donde se almacenan los mensajes.
 */
function display_transient_messages($transient_name) {
    if ($messages = get_transient($transient_name)) {
        foreach ($messages as $message) {
            echo "<div class='{$message['type']}'>
                <p>{$message['message']}</p>
            </div>";
        }
        delete_transient($transient_name);
    }
}

/**
 * Registra mensajes en un archivo de log específico del plugin.
 *
 * @param string $message El mensaje a registrar.
 */
function plugin_log($message) {
    if (WP_DEBUG === true) {
        // Especifica la ruta del archivo de log. Asegúrate de que este archivo es escribible por el servidor web.
        $log_file = __DIR__ . '/plugin-log.txt';
        // Construye el mensaje con la fecha y hora actual.
        $log_message = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
        // Añade el mensaje al archivo de log.
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}


/**
 * Guarda un mensaje en un transitorio para ser mostrado después.
 *
 * @param string $transient_name El nombre del transitorio donde almacenar el mensaje.
 * @param string $message El mensaje a almacenar.
 * @param string $type El tipo de mensaje ('updated' para éxito, 'error' para errores).
 */
function set_transient_message($transient_name, $message, $type = 'updated') {
    set_transient($transient_name, [['message' => $message, 'type' => $type]], 60);
}

/**
 * Verifica si un valor es un número entero positivo.
 *
 * @param mixed $value El valor a verificar.
 * @return bool Verdadero si el valor es un entero positivo, falso en caso contrario.
 */
function is_positive_integer($value) {
    return (filter_var($value, FILTER_VALIDATE_INT) !== false) && ($value > 0);
}


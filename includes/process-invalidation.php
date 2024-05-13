<?php

if (!defined('ABSPATH')) exit;

// Acciones para manejar la invalidación y la consulta de estado
add_action('admin_post_invalidate_cache', 'process_invalidation_request');



// Función principal para manejar la invalidación
function process_invalidation_request() {
    if(!isset($_POST['invalidar']))
        return;

    $accessKey = sanitize_text_field_custom(get_option('aws_access_key'));
    $secretKey = sanitize_text_field_custom(get_option('aws_secret_key'));
    $distributionIds1 = sanitize_text_field_custom(get_option('aws_distribution_id_1'));
    $distributionIds2 = sanitize_text_field_custom(get_option('aws_distribution_id_2'));
    

    if(empty($distributionIds1) || empty($distributionIds2)) {
        set_transient_message('invalidate_cache_feedback', 'Por favor, especifica al menos un ID de distribución para AWS CloudFront.', 'error');
        return;
    }

        $distributionIdsArray = array($distributionIds1, $distributionIds2);

         invalidate_cache($accessKey, $secretKey, $distributionIds1,$distributionIds2);
}

 function invalidate_cache($accessKey, $secretKey, $distributionId1,$distributionId2, $customPaths = '') {
    $pathsInput = !empty($customPaths) ? $customPaths : (isset($_POST['paths']) ? sanitize_text_field($_POST['paths']) : '');
    $pathsArray = array_filter(explode(',', $pathsInput));
   
    if (empty($pathsArray)) {
        set_transient_message('invalidate_cache_feedback', 'Por favor, especifica al menos un path para invalidar.', 'error');
        return;
    }

    $pathsForInvalidation = array_map(function($path) {
        return '/' . ltrim($path, '/');
    }, $pathsArray);

    $callerReference = 'invalidate-' . time();

    $cloudFrontClient = new Aws\CloudFront\CloudFrontClient([
        'version' => 'latest',
        'region' => 'us-east-1',
        'credentials' => [
            'key'    => $accessKey,
            'secret' => $secretKey,
        ],
    ]);
    
    try {
        $result = $cloudFrontClient->createInvalidation([
            'DistributionId' => $distributionId1,
            'InvalidationBatch' => [
                'CallerReference' => $callerReference,
                'Paths' => [
                    'Items' => $pathsForInvalidation,
                    'Quantity' => count($pathsForInvalidation),
                ],
            ]
        ]);

        $result2 = $cloudFrontClient->createInvalidation([
            'DistributionId' => $distributionId2,
            'InvalidationBatch' => [
                'CallerReference' => $callerReference,
                'Paths' => [
                    'Items' => $pathsForInvalidation,
                    'Quantity' => count($pathsForInvalidation),
                ],
            ]
        ]);

        $message = '';
        if (isset($result['Location'])) {
            $message = 'The invalidation location is: ' . $result['Location'];
        }
        $message .= ' and the effective URI is ' . $result['@metadata']['effectiveUri'] . '.';
        
        // Mostrar mensaje de éxito
        set_transient_message('invalidate_cache_feedback', $message, 'updated');
        set_transient_message('invalidate_cache_feedback',
            'La Invalidación se envió correctamente. Revisa el sitio en unos minutos para visualizar los cambios.', 'updated');

    } catch (Aws\Exception\AwsException $e) {
        // Mostrar mensaje de error
        set_transient_message('invalidate_cache_feedback', 'Ocurrió un error al realizar la invalidación. Revisa que las rutas estén ingresadas correctamente e intenta de nuevo.

        Si el problema continúa, comunícate con el equipo de administración. ' . $message, 'error');
    }
    
    wp_redirect(admin_url( '/admin.php?page=invalidar-cache'));
    exit();

}

?>

<?php

add_action('wp_ajax_chatbot_get_question', 'chatbot_get_question');
add_action('wp_ajax_nopriv_chatbot_get_question', 'chatbot_get_question');



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chatbot_season'])) {
    update_option('chatbot_season', sanitize_text_field($_POST['chatbot_season']));
}
function chatbot_get_question() {
    $chatbot_data = get_option('chatbot_data', '[]');
    $questions = json_decode($chatbot_data, true); 

    $question_id = intval($_POST['question_id'] ?? 0);

    $response = $questions[$question_id] ?? null;

    if ($response) {
        wp_send_json_success($response); // Incluye "success: true" en la respuesta
    } else {
        wp_send_json_error(['error' => 'Pregunta no encontrada']); // Incluye "success: false"
    }

}
function chatbot_export_options() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No tienes permisos para realizar esta acción.' );
    }

    // Obtener las opciones del chatbot
    $questions = get_option( 'chatbot_data', array() );

    // Configurar el archivo de exportación
    header( 'Content-Type: application/json' );
    header( 'Content-Disposition: attachment; filename="chatbot-questions.json"' );
    echo json_encode( $questions );
    exit;
}
add_action( 'admin_post_chatbot_export', 'chatbot_export_options' );

function chatbot_import_options() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No tienes permisos para realizar esta acción.' );
    }

    if ( isset( $_FILES['chatbot_questions_file'] ) && $_FILES['chatbot_questions_file']['error'] === UPLOAD_ERR_OK ) {
        $file = $_FILES['chatbot_questions_file']['tmp_name'];
        $data = file_get_contents( $file );

        // Decodificar JSON
        $questions = json_decode( $data, true );

        if ( json_last_error() === JSON_ERROR_NONE ) {
            // Guardar las preguntas importadas
            update_option( 'chatbot_data', $questions );
            wp_redirect( admin_url( 'options-general.php?page=chatbot-settings&import=success' ) );
            exit;
        } else {
            wp_redirect( admin_url( 'options-general.php?page=chatbot-settings&import=error' ) );
            exit;
        }
    }

    wp_redirect( admin_url( 'options-general.php?page=chatbot-settings&import=error' ) );
    exit;
}
add_action( 'admin_post_chatbot_import', 'chatbot_import_options' );

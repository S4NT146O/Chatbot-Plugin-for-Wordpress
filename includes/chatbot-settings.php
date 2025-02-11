<?php

function chatbot_admin_menu() {
    add_menu_page(
        'Chatbot',
        'Chatbot',
        'manage_options',
        'custom-chatbot',
        'chatbot_settings_page',
        'dashicons-format-chat',
        20
    );
}

add_action('admin_menu', 'chatbot_admin_menu');


function chatbot_settings_page() {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['chatbot_season'])) {
        update_option('chatbot_season', sanitize_text_field($_POST['chatbot_season']));
    }

    if (isset($_POST['questions'])) {
        $questions = [];

        foreach ($_POST['questions'] as $id => $question) {
            $questionData = [
                'id' => intval($id),
                'question' => sanitize_text_field($question['question']),
                'options' => []
            ];
               
            if (!empty($question['options'])) {
                foreach ($question['options'] as $index => $option) {
                    $questionData['options'][] = [
                        'text' => sanitize_text_field($option['text']),
                        'next' => isset($option['next']) && $option['next'] !== '' ? intval($option['next']) : null,
                        'link' => !empty($option['link']) ? esc_url_raw($option['link']) : null,
                    ];
                }
            }

            $questions[$id] = $questionData;
        }

        update_option('chatbot_data', wp_json_encode($questions));
    }
}

    $chatbot_data = get_option('chatbot_data', '[]');
    $questions = json_decode($chatbot_data, true) ?: [];

    ?>
    <form method="post" id="chatbot-settings-form">
    <h2>Configuración de imagen del bot</h2>
    <label for="chatbot_season">Selecciona la temporada:</label>
    <select name="chatbot_season" id="chatbot_season">
        <option value="invierno" <?php selected(get_option('chatbot_season'), 'invierno'); ?>>Invierno</option>
        <option value="verano" <?php selected(get_option('chatbot_season'), 'verano'); ?>>Verano</option>
    </select>
    
    <button type="submit" class="button button-primary">Guardar Configuración</button>

    <div id="chatbot-questions-container">
        <?php foreach ($questions as $id => $question): ?>
            <div class="chatbot-question-item" data-id="<?php echo esc_attr($id); ?>">
                <h3>Pregunta ID: <?php echo esc_html($id); ?></h3>
                <label>Pregunta:</label>
                <input type="text" name="questions[<?php echo esc_attr($id); ?>][question]" value="<?php echo esc_attr($question['question']); ?>" style="width: 100%;" />

                <h4>Opciones:</h4>
                <div class="chatbot-options-container">
                    <?php foreach ($question['options'] as $index => $option): ?>
                        <div class="chatbot-option-item">
                            <label>Texto:</label>
                            <input type="text" name="questions[<?php echo esc_attr($id); ?>][options][<?php echo $index; ?>][text]" value="<?php echo esc_attr($option['text']); ?>" />
                            <label>Próxima Pregunta (ID):</label>
                            <input type="number" name="questions[<?php echo esc_attr($id); ?>][options][<?php echo $index; ?>][next]" value="<?php echo esc_attr($option['next']); ?>" />
                            <label>Enlace (opcional):</label>
                            <input type="url" name="questions[<?php echo esc_attr($id); ?>][options][<?php echo $index; ?>][link]" value="<?php echo esc_attr($option['link'] ?? ''); ?>" />
                            <button type="button" class="button remove-option">Eliminar Opción</button>
                        </div>
                    <?php endforeach; ?>
                    <button type="button" class="button add-option">Añadir Opción</button>
                </div>
                <button type="button" class="button remove-question">Eliminar Pregunta</button>
            </div>
        <?php endforeach; ?>
    </div>
        <button type="button" class="button button-secondary" id="add-question">Añadir Pregunta</button>
        <button type="submit" class="button button-primary">Guardar</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('chatbot-questions-container');

            let questionCounter = <?php echo count($questions); ?>; 

            document.getElementById('add-question').addEventListener('click', () => {
                const id = questionCounter++; 
                const questionHTML = `
                    <div class="chatbot-question-item" data-id="${id}">
                        <h3>Pregunta ID: ${id}</h3>
                        <label>Pregunta:</label>
                        <input type="text" name="questions[${id}][question]" style="width: 100%;" />
                        <h4>Opciones:</h4>
                        <div class="chatbot-options-container"></div>
                        <button type="button" class="button add-option">Añadir Opción</button>
                        <button type="button" class="button remove-question">Eliminar Pregunta</button>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', questionHTML);
            });

            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('add-option')) {
                    const parent = event.target.closest('.chatbot-question-item');
                    const questionId = parent.getAttribute('data-id');
                    const optionsContainer = parent.querySelector('.chatbot-options-container');
                    const optionIndex = optionsContainer.children.length;

                    const optionHTML = `
                        <div class="chatbot-option-item">
                            <label>Texto:</label>
                            <input type="text" name="questions[${questionId}][options][${optionIndex}][text]" />
                            <label>Próxima Pregunta (ID):</label>
                            <input type="number" name="questions[${questionId}][options][${optionIndex}][next]" />
                            <label>Enlace (opcional):</label>
                            <input type="url" name="questions[${questionId}][options][${optionIndex}][link]" />
                            <button type="button" class="button remove-option">Eliminar Opción</button>
                        </div>
                    `;

                    optionsContainer.insertAdjacentHTML('beforeend', optionHTML);
                } else if (event.target.classList.contains('remove-option')) {
                    event.target.closest('.chatbot-option-item').remove();
                } else if (event.target.classList.contains('remove-question')) {
                    event.target.closest('.chatbot-question-item').remove();
                }
            });
        });
    </script>
    <?php if (isset($_GET['import_success'])): ?>
    <div class="notice notice-success is-dismissible">
        <p>Las preguntas se han importado correctamente.</p>
    </div>
    <?php elseif (isset($_GET['import_error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>Error al importar las preguntas. Asegúrate de que el archivo sea un JSON válido.</p>
        </div>
    <?php endif; ?>

    <h2>Exportar preguntas</h2>
<a href="<?php echo admin_url( 'admin-post.php?action=chatbot_export' ); ?>" class="button">Exportar preguntas</a>

<h2>Importar preguntas</h2>
<form method="post" action="<?php echo admin_url( 'admin-post.php?action=chatbot_import' ); ?>" enctype="multipart/form-data">
    <input type="file" name="chatbot_questions_file" accept=".json" required>
    <button type="submit" class="button button-primary">Importar preguntas</button>
</form>


    <style>
        .chatbot-question-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            background: #f9f9f9;
        }

        .chatbot-option-item {
            margin-bottom: 10px;
        }

        .remove-option, .remove-question {
            margin-top: 10px;
            color: #b32d2e;
        }

        .add-option {
            margin-top: 10px;
        }
    </style>
    <?php
}
?>

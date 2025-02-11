jQuery(document).ready(function ($) {
    const season = chatbotData.season;
    const botImage = season === 'invierno' 
        ? 'https://www.malargue.tur.ar/wp-content/plugins/custom-chatbot/assets/invierno.png' 
        : 'https://www.malargue.tur.ar/wp-content/plugins/custom-chatbot/assets/verano.png';

    // Agregar el chatbot al cuerpo del documento con botón de cierre
    const chatbot = $(`
        <div id="chatbot-sphere">
            <img src="${botImage}" alt="Chatbot">
        </div>
        <div id="chatbot-window" style="display: none;">
            <div id="chatbot-header">
                <span id="chatbot-close">&times;</span>
            </div>
            <div id="chatbot-messages"></div>
        </div>
    `).appendTo('body');

    const chatWindow = $('#chatbot-window');

    // Mostrar/ocultar chatbot al hacer clic en el ícono
    document.addEventListener('click', function(event) {
        let chatbotWindow = document.getElementById('chatbot-window');
        let chatbotSphere = document.getElementById('chatbot-sphere');
        let closeBtn = document.getElementById('chatbot-close');
    
        // Si el clic es en el botón de cerrar, oculta la ventana
        if (event.target === closeBtn) {
            chatbotWindow.style.display = 'none';
        } 
        // Si el clic es en el ícono del chatbot, muestra la ventana
        else if (event.target === chatbotSphere || chatbotSphere.contains(event.target)) {
            chatbotWindow.style.display = chatbotWindow.style.display === 'none' ? 'block' : 'none';
        } 
        // Si el clic está fuera del chatbot, ciérralo
        else if (!chatbotWindow.contains(event.target)) {
            chatbotWindow.style.display = 'none';
        }
    });
    
    


    // Animación para llamar la atención después de inactividad
    function attentionAnimation() {
        setTimeout(() => {
            $('#chatbot-sphere').addClass('attention-bounce');
            setTimeout(() => {
                $('#chatbot-sphere').removeClass('attention-bounce');
            }, 1000);
        }, 5000);
    }

    attentionAnimation();  // Iniciar animación de atención

    // Cargar la primera pregunta
    function loadQuestion(questionId) {
        $.post(chatbotData.ajaxUrl, { action: 'chatbot_get_question', question_id: questionId }, function (response) {
            console.log('Respuesta del servidor:', response);
        
            if (response.success) {
                $('#chatbot-messages').empty().append(`
                    <div class="chatbot-question">${response.data.question}</div>
                `);

                response.data.options.forEach(option => {
                    $('#chatbot-messages').append(`
                        <button class="chatbot-option" 
                                data-next="${option.next || ''}" 
                                data-link="${option.link || ''}">
                            ${option.text}
                        </button>
                    `);
                });
            } else {
                console.error('Error en la respuesta del servidor:', response.data.error);
                $('#chatbot-messages').empty().append('<div class="chatbot-question">Error al cargar las opciones.</div>');
            }
        }).fail(function (error) {
            console.error('Error en la solicitud AJAX:', error);
            $('#chatbot-messages').empty().append('<div class="chatbot-question">Error en la comunicación con el servidor.</div>');
        });
    }

    loadQuestion(0); // Cargar la primera pregunta
    
    // Manejar los clics en las opciones del chatbot
    $('#chatbot-messages').on('click', '.chatbot-option', function () {
        const nextQuestionId = $(this).data('next');
        const link = $(this).data('link');

        if (link) {
            window.open(link, '_blank');
        } else if (nextQuestionId !== null && nextQuestionId !== '') {
            loadQuestion(nextQuestionId);
        } else {
            console.error('La opción seleccionada no tiene un enlace ni un ID válido.');
        }
    });
});

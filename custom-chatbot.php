<?php
/*
Plugin Name: Custom Chatbot
Description: Chatbot con preguntas y respuestas definidas desde el dashboard.
Version: 1.0
Author: Santiago N. zapata
*/

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/chatbot-settings.php';

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('chatbot-style', plugin_dir_url(__FILE__) . 'assets/chatbot.css');
    wp_enqueue_script('custom-chatbot', get_site_url() . '/wp-content/plugins/custom-chatbot/assets/chatbot.js', array('jquery'), null, true);
    wp_localize_script('chatbot-script', 'chatbotData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
});

require_once plugin_dir_path(__FILE__) . 'includes/chatbot-logic.php';

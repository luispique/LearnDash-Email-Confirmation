<?php
/**
 * Plugin Name: LearnDash Email Confirmation
 * Plugin URI:  https://luispique.com/learndash-email-confirmation
 * Description: Adds an email confirmation step to the LearnDash registration process, requiring users to confirm their email address before accessing content.
 * Version:     1.0.1
 * Author:      Luis Pique
 * Author URI:  https://luispique.com
 * Text Domain: learndash-email-confirmation
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

header('Content-Type: text/html; charset=utf-8');

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-functions.php';
}

register_activation_hook(__FILE__, 'ld_email_confirmation_activate');
function ld_email_confirmation_activate() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('sfwd-lms/sfwd_lms.php')) {
        wp_die('This plugin requires LearnDash LMS to be installed and activated.');
    }
}

add_action('user_register', 'ld_email_confirmation_send_email');
function ld_email_confirmation_send_email($user_id) {
    $user_info = get_userdata($user_id);
    $user_email = sanitize_email($user_info->user_email);
    $user_name = $user_info->display_name;
    $site_name = get_bloginfo('name');
    $key = sha1(time() . $user_email . wp_rand());
    update_user_meta($user_id, 'has_confirmed_email', 'no');
    update_user_meta($user_id, 'confirm_email_key', $key);
    $confirmation_link = home_url('/') . '?' . http_build_query(['action' => 'confirm_email', 'key' => $key, 'user' => $user_id]);

    $subject = str_replace(['[site]', '[name]'], [$site_name, $user_name], get_option('ld_email_confirmation_subject'));
    $message = str_replace(['[site]', '[name]', '[link]'], [$site_name, $user_name, $confirmation_link], get_option('ld_email_confirmation_message'));

    $headers = ['Content-Type: text/html; charset=UTF-8', 'From: ' . get_option('ld_from_name') . ' <' . get_option('ld_from_email') . '>'];
    error_log("Sending email to: $user_email"); // Log para depuración
    error_log("Subject: $subject");
    error_log("Message: $message");
    error_log("Headers: " . print_r($headers, true));

    $sent = wp_mail($user_email, $subject, $message, $headers);
    if (!$sent) {
        error_log("Failed to send email to $user_email");
    }

    set_transient('ld-registered-notice', true, 60);
    wp_redirect(add_query_arg('registered', 'true', home_url()));
    exit;
}

function send_welcome_email($user_id) {
    $user_info = get_userdata($user_id);
    if (!$user_info) return;

    $user_email = $user_info->user_email;
    $user_name = $user_info->display_name;
    $site_name = get_bloginfo('name');

    $subject = str_replace('[site]', $site_name, get_option('ld_welcome_email_subject'));
    $message = str_replace(['[site]', '[name]'], [$site_name, $user_name], get_option('ld_welcome_email_message'));

    $headers = ['Content-Type: text/html; charset=UTF-8', 'From: ' . get_option('ld_from_name') . ' <' . get_option('ld_from_email') . '>'];
    wp_mail($user_email, $subject, $message, $headers);
}

add_action('init', 'ld_handle_email_confirmation');
function ld_handle_email_confirmation() {
    if (isset($_GET['action'], $_GET['user']) && $_GET['action'] === 'confirm_email') {
        $user_id = intval($_GET['user']);
        $key_received = sanitize_text_field($_GET['key']);
        $key_expected = get_user_meta($user_id, 'confirm_email_key', true);

        if ($key_received === $key_expected) {
            update_user_meta($user_id, 'has_confirmed_email', 'yes');
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            set_transient('ld-confirmed-notice', true, 60);
            wp_redirect(add_query_arg('confirmed', 'true', home_url('/registro-exitoso')));
            send_welcome_email($user_id);
            exit;
        } else {
            set_transient('ld-confirmation-failed-notice', true, 60);
            wp_redirect(add_query_arg('confirmation', 'failed', home_url('/error-clave-no-valida')));
            exit;
        }
    }
}

add_action('wp_footer', 'ld_display_notices');
function ld_display_notices() {
    // Implementar la lógica para mostrar avisos en el pie de página
}

// Aquí puedes añadir las nuevas funciones para probar el envío de correo

add_action('admin_notices', function() {
    if (get_transient('ld-test-email')) {
        echo '<div class="notice notice-success"><p>Test email sent successfully.</p></div>';
        delete_transient('ld-test-email');
    }
});

add_action('admin_init', function() {
    if (isset($_GET['send_test_email'])) {
        $sent = wp_mail(get_option('admin_email'), 'Test Mail', 'This is a test email.', ['Content-Type: text/html; charset=UTF-8']);
        if ($sent) {
            set_transient('ld-test-email', true, 60);
        }
        wp_redirect(remove_query_arg('send_test_email'));
        exit;
    }
});

add_action('admin_menu', function() {
    add_submenu_page('ld_email_settings', 'Send Email Test', 'Send Email Test', 'manage_options', 'send_test_email', function() {
        ?>
        <div class="wrap">
            <h1>Send Email Test</h1>
            <form method="post" action="?send_test_email=1">
                <?php submit_button('Send Email Test'); ?>
            </form>
        </div>
        <?php
    });
});

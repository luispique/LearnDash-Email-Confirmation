<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Registrar opciones para la configuración de correos electrónicos
function ld_register_plugin_settings() {
    add_option('ld_email_confirmation_subject', 'Please confirm your email address for [site]');
    add_option('ld_email_confirmation_message', 'Hi [name]. Thank you for signing up. Please confirm your email address by clicking here: [link].');
    add_option('ld_welcome_email_subject', '🎉 Registration completed! Welcome to [site]');
    add_option('ld_welcome_email_message', 'Hi [name], Welcome to [site]! We are excited to have you on board.');
    add_option('ld_from_name', get_bloginfo('name'));
    add_option('ld_from_email', get_bloginfo('admin_email'));

    register_setting('ld_options_group', 'ld_email_confirmation_subject');
    register_setting('ld_options_group', 'ld_email_confirmation_message');
    register_setting('ld_options_group', 'ld_welcome_email_subject');
    register_setting('ld_options_group', 'ld_welcome_email_message');
    register_setting('ld_options_group', 'ld_from_name');
    register_setting('ld_options_group', 'ld_from_email');
}

add_action('admin_init', 'ld_register_plugin_settings');

// Añadir menú en el área de administración
function ld_add_settings_page() {
    add_menu_page('LearnDash Email Setup', 'LD Email Setup', 'manage_options', 'ld_email_settings', 'ld_settings_page_html', 'dashicons-email');
}

add_action('admin_menu', 'ld_add_settings_page');

// HTML de la página de configuración
function ld_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('ld_options_group');
            do_settings_sections('ld_options_group');
            ?>
            <h2>Confirmation Email</h2>
            <p>
                <label>Asunto:</label>
                <input type="text" name="ld_email_confirmation_subject" value="<?php echo esc_attr(get_option('ld_email_confirmation_subject')); ?>" size="70">
            </p>
            <p>
                <label>Mensaje:</label>
                <textarea name="ld_email_confirmation_message" rows="5" cols="70"><?php echo esc_attr(get_option('ld_email_confirmation_message')); ?></textarea>
            </p>
            <h2>Welcome Email</h2>
            <p>
                <label>Asunto:</label>
                <input type="text" name="ld_welcome_email_subject" value="<?php echo esc_attr(get_option('ld_welcome_email_subject')); ?>" size="70">
            </p>
            <p>
                <label>Mensaje:</label>
                <textarea name="ld_welcome_email_message" rows="5" cols="70"><?php echo esc_attr(get_option('ld_welcome_email_message')); ?></textarea>
            </p>
            <h2>Sender settings</h2>
            <p>
                <label>Sender's name:</label>
                <input type="text" name="ld_from_name" value="<?php echo esc_attr(get_option('ld_from_name')); ?>" size="70">
            </p>
            <p>
                <label>Sender's email:</label>
                <input type="email" name="ld_from_email" value="<?php echo esc_attr(get_option('ld_from_email')); ?>" size="70">
            </p>
            <?php
            submit_button('Save changes');
            ?>
        </form>
    </div>
    <?php
}

// Agregar una nueva columna al listado de usuarios
function ld_add_user_confirmed_column($columns) {
    $columns['email_confirmed'] = __('Account Confirmed', 'learndash-email-confirmation');
    return $columns;
}
add_filter('manage_users_columns', 'ld_add_user_confirmed_column');

// Rellenar la nueva columna con el estado de confirmación del usuario
function ld_show_user_confirmed_column_content($value, $column_name, $user_id) {
    if ('email_confirmed' === $column_name) {
        $is_confirmed = get_user_meta($user_id, 'has_confirmed_email', true);
        return $is_confirmed === 'yes' ? __('Yes', 'learndash-email-confirmation') : __('No', 'learndash-email-confirmation');
    }
    return $value;
}
add_filter('manage_users_custom_column', 'ld_show_user_confirmed_column_content', 10, 3);

// Agregar un enlace de acción de confirmación manual
function ld_add_confirm_user_link($actions, $user_object) {
    if (get_user_meta($user_object->ID, 'has_confirmed_email', true) !== 'yes') {
        $actions['confirm_user'] = sprintf('<a href="%s">%s</a>', wp_nonce_url(add_query_arg(['action' => 'ld_confirm_user', 'user' => $user_object->ID], admin_url('users.php')), 'ld_confirm_user_' . $user_object->ID), __('Confirm Account', 'learndash-email-confirmation'));
    }
    return $actions;
}
add_filter('user_row_actions', 'ld_add_confirm_user_link', 10, 2);

// Procesar la confirmación manual del usuario
function ld_process_manual_user_confirmation() {
    if (isset($_GET['action'], $_GET['user']) && $_GET['action'] === 'ld_confirm_user') {
        $user_id = absint($_GET['user']);
        if (!current_user_can('edit_user', $user_id) || !wp_verify_nonce($_GET['_wpnonce'], 'ld_confirm_user_' . $user_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'learndash-email-confirmation'));
        }

        update_user_meta($user_id, 'has_confirmed_email', 'yes');
        wp_redirect(remove_query_arg(['action', 'user', '_wpnonce']));
        exit;
    }
}
add_action('admin_init', 'ld_process_manual_user_confirmation');

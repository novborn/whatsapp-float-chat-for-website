<?php

/**
 * @package CPG Float Whatsapp Chat for Website
 * @version 1.0
 */
/*
Plugin Name: Float Whatsapp Chat for Website
Plugin URI: https://www.unipro.co.in/
Description: A plugin to add a floating WhatsApp button on your website with custom mobile number and title settings.
Author: Chandra
Version: 1.1
Author URI: https://www.unipro.co.in/
*/


if (!defined('ABSPATH')) {
    exit;
}

// Enqueue CSS and JS files
function cpg_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style(
        'cpg-float-whatsapp-style',  
        plugin_dir_url(__FILE__) . 'assets/css/floating-wpp.css',  
        array(),  
        '1.0',  
        'all'   
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'cpg-float-whatsapp-script',  
        plugin_dir_url(__FILE__) . 'assets/js/floating-wpp.js',  
        array('jquery'),  
        '1.0',  
        true    
    );
}
add_action('wp_enqueue_scripts', 'cpg_enqueue_scripts');


// Create a table for storing mobile number and title upon plugin activation
function cpg_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_float_settings';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        phone_number varchar(15) NOT NULL,
        title varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cpg_create_table');

// Add a settings page
function cpg_add_admin_menu() {
    add_menu_page(
        'WhatsApp Float Settings', 
        'WhatsApp Float', 
        'manage_options', 
        'cpg-whatsapp-float', 
        'cpg_settings_page', 
        'dashicons-whatsapp', 
        90
    );
}
add_action('admin_menu', 'cpg_add_admin_menu');

// Display the settings page content
function cpg_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_float_settings';

    if (isset($_POST['submit'])) {
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $title = sanitize_text_field($_POST['title']);
        
        // Insert or update settings
        $wpdb->insert(
            $table_name, 
            array(
                'phone_number' => $phone_number,
                'title' => $title
            )
        );
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Fetch the latest saved data
    $settings = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");
    
    $phone_number = isset($settings->phone_number) ? esc_attr($settings->phone_number) : '';
    $title = isset($settings->title) ? esc_attr($settings->title) : '';
    
    ?>
    <div class="wrap">
        <h1>WhatsApp Float Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Phone Number</th>
                    <td><input type="text" name="phone_number" value="<?php echo $phone_number; ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Title</th>
                    <td><input type="text" name="title" value="<?php echo $title; ?>" required /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" class="button-primary" value="Save Changes" />
            </p>
        </form>
    </div>
    <?php
}


function cpg_float_whatsapp_button() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_float_settings';
    
    // Get the latest phone number and title
    $settings = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");

    $phone_number = isset($settings->phone_number) ? esc_attr($settings->phone_number) : '919540098976'; // Default number
    $title = isset($settings->title) ? esc_attr($settings->title) : 'Chat with Chandra'; // Default title
    
    $whatsapp_icon_url = plugin_dir_url(__FILE__) . 'assets/images/whatsapp.svg';
    ?>
    <div id="my_whatsapp_btn"></div>
    
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#my_whatsapp_btn').floatingWhatsApp({
                phone: '<?php echo $phone_number; ?>',
                popupMessage: 'Hello, How can we help you?',
                message: 'Hi',
                size: '50px',
                showPopup: true,
                showOnIE: false,
                headerTitle: '<?php echo $title; ?>',
                headerColor: '#0a5f54',
                backgroundColor: '#43c553',
                buttonImage: '<img src="<?php echo esc_url($whatsapp_icon_url); ?>" />'
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'cpg_float_whatsapp_button');

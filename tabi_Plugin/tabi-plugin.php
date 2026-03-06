<?php
/**
 * Plugin Name: Tabi Plugins by Franklyn
 * Description: Custom button, Elementor swiper and popup.
 * Version: 1.0
 * Author: Gabriely Muniz and Felipe Coutinho
 */

if (!defined('ABSPATH')) exit;

define('TABI_PLUGIN_VERSION', '1.0.1');

function tabi_should_show_popup() {
    $enabled = get_option('tabi_popup_enabled');
    if ( ! $enabled ) {
        return false;
    }

    $pages = get_option('tabi_popup_pages', '');
    if ( empty( $pages ) ) {
        return true;
    }

    $page_ids = array_map( 'intval', explode( ',', $pages ) );
    return in_array( get_queried_object_id(), $page_ids, true );
}

function tabi_enqueue_assets() {
    wp_enqueue_style(
        'tabi-buttons',
        plugin_dir_url(__FILE__) . 'css/buttons.css',
        array(),
        TABI_PLUGIN_VERSION
    );

    wp_enqueue_style(
        'tabi-swiper',
        plugin_dir_url(__FILE__) . 'css/swipper.css',
        array(),
        TABI_PLUGIN_VERSION
    );

    $show = tabi_should_show_popup();

    if ( $show ) {
        wp_enqueue_style(
            'tabi-typekit',
            'https://use.typekit.net/jtf2xza.css',
            array(),
            null
        );

        wp_enqueue_style(
            'tabi-popup',
            plugin_dir_url(__FILE__) . 'css/popup.css',
            array(),
            TABI_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'tabi-popup-js',
            plugin_dir_url(__FILE__) . 'js/popup.js',
            array('jquery'),
            TABI_PLUGIN_VERSION,
            true
        );

        $scroll = get_option('tabi_popup_scroll', 80);
        $delay = get_option('tabi_popup_delay', 3);
        $trigger = get_option('tabi_popup_trigger_type', 'scroll');
        wp_localize_script('tabi-popup-js', 'tabiSettings', array(
            'scrollPoint' => $scroll,
            'delay'       => $delay * 1000,
            'triggerType' => $trigger,
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('tabi_track_view_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'tabi_enqueue_assets');

function tabi_render_popup() {
    $show = tabi_should_show_popup();

    if ( $show ) {
        include plugin_dir_path(__FILE__) . 'templates/popup.php';
    }
}
add_action('wp_footer', 'tabi_render_popup');

function tabi_enqueue_admin_scripts($hook_suffix) {
    // Only load on our specific admin page
    if ($hook_suffix !== 'toplevel_page_tabi-popup-settings') {
        return;
    }
    // Enqueue WordPress's built-in color picker
    wp_enqueue_style('wp-color-picker');
    
    // Enqueue Popup CSS for the preview
    wp_enqueue_style(
        'tabi-popup-preview-css',
        plugin_dir_url(__FILE__) . 'css/popup.css',
        array(),
        TABI_PLUGIN_VERSION
    );
    
    // Enqueue Typekit for the preview
    wp_enqueue_style(
        'tabi-typekit',
        'https://use.typekit.net/jtf2xza.css'
    );

    wp_enqueue_script(
        'tabi-admin-js',
        plugin_dir_url(__FILE__) . 'js/admin.js',
        array('wp-color-picker', 'jquery'), TABI_PLUGIN_VERSION, true
    );
}
add_action('admin_enqueue_scripts', 'tabi_enqueue_admin_scripts');

function tabi_admin_menu() {
    add_menu_page(
        'Popup Settings',
        'Popup Settings',
        'manage_options',
        'tabi-popup-settings',
        'tabi_settings_page',
        'dashicons-megaphone',
        20
    );
}
add_action('admin_menu', 'tabi_admin_menu');

function tabi_settings_page() {
    if ( isset( $_POST['tabi_save'] ) ) {
        if ( ! isset( $_POST['tabi_popup_nonce'] ) || ! wp_verify_nonce( $_POST['tabi_popup_nonce'], 'tabi_save_action' ) ) {
            echo '<div class="error"><p>' . esc_html__( 'Security check failed.', 'tabi-plugin' ) . '</p></div>';
        } else {
            // General Settings
            update_option('tabi_popup_enabled', isset($_POST['popup_enabled']) ? 1 : 0);
            update_option('tabi_popup_pages', isset($_POST['popup_pages']) ? sanitize_text_field($_POST['popup_pages']) : '');
            update_option('tabi_popup_position', isset($_POST['popup_position']) ? sanitize_key($_POST['popup_position']) : 'center');
            update_option('tabi_popup_width', isset($_POST['popup_width']) ? sanitize_key($_POST['popup_width']) : 'default');
            update_option('tabi_popup_trigger_type', isset($_POST['popup_trigger_type']) ? sanitize_key($_POST['popup_trigger_type']) : 'scroll');
            update_option('tabi_popup_scroll', isset($_POST['popup_scroll']) ? intval($_POST['popup_scroll']) : 80);
            update_option('tabi_popup_delay', isset($_POST['popup_delay']) ? intval($_POST['popup_delay']) : 3);
            
            // Content Settings
            update_option('tabi_popup_headline', isset($_POST['popup_headline']) ? sanitize_text_field($_POST['popup_headline']) : '');
            update_option('tabi_popup_headline_font_size', isset($_POST['popup_headline_font_size']) ? intval($_POST['popup_headline_font_size']) : 28);
            update_option('tabi_popup_headline_font_size_mobile', isset($_POST['popup_headline_font_size_mobile']) ? intval($_POST['popup_headline_font_size_mobile']) : 24);
            update_option('tabi_popup_headline_font_family', isset($_POST['popup_headline_font_family']) ? sanitize_text_field($_POST['popup_headline_font_family']) : 'sans-serif');
            update_option('tabi_popup_headline_font_weight', isset($_POST['popup_headline_font_weight']) ? sanitize_text_field($_POST['popup_headline_font_weight']) : '500');
            update_option('tabi_popup_headline_font_color', isset($_POST['popup_headline_font_color']) ? sanitize_hex_color($_POST['popup_headline_font_color']) : '#000000');
            update_option('tabi_popup_headline_alignment', isset($_POST['popup_headline_alignment']) ? sanitize_key($_POST['popup_headline_alignment']) : 'center');
            update_option('tabi_popup_headline_alignment_mobile', isset($_POST['popup_headline_alignment_mobile']) ? sanitize_key($_POST['popup_headline_alignment_mobile']) : 'center');

            update_option('tabi_popup_paragraph', isset($_POST['popup_paragraph']) ? sanitize_textarea_field($_POST['popup_paragraph']) : '');
            update_option('tabi_popup_paragraph_font_size', isset($_POST['popup_paragraph_font_size']) ? intval($_POST['popup_paragraph_font_size']) : 16);
            update_option('tabi_popup_paragraph_font_size_mobile', isset($_POST['popup_paragraph_font_size_mobile']) ? intval($_POST['popup_paragraph_font_size_mobile']) : 14);
            update_option('tabi_popup_paragraph_font_family', isset($_POST['popup_paragraph_font_family']) ? sanitize_text_field($_POST['popup_paragraph_font_family']) : 'sans-serif');
            update_option('tabi_popup_paragraph_font_weight', isset($_POST['popup_paragraph_font_weight']) ? sanitize_text_field($_POST['popup_paragraph_font_weight']) : '400');
            update_option('tabi_popup_paragraph_font_color', isset($_POST['popup_paragraph_font_color']) ? sanitize_hex_color($_POST['popup_paragraph_font_color']) : '#333333');
            update_option('tabi_popup_paragraph_alignment', isset($_POST['popup_paragraph_alignment']) ? sanitize_key($_POST['popup_paragraph_alignment']) : 'center');
            update_option('tabi_popup_paragraph_alignment_mobile', isset($_POST['popup_paragraph_alignment_mobile']) ? sanitize_key($_POST['popup_paragraph_alignment_mobile']) : 'center');

            update_option('tabi_popup_button', isset($_POST['popup_button']) ? sanitize_text_field($_POST['popup_button']) : '');
            update_option('tabi_popup_button_url', isset($_POST['popup_button_url']) ? sanitize_url($_POST['popup_button_url']) : '');
            update_option('tabi_popup_bg_color', isset($_POST['popup_bg_color']) ? sanitize_hex_color($_POST['popup_bg_color']) : '');
            
            echo '<div class="updated"><p>' . esc_html__( 'Settings saved successfully!', 'tabi-plugin' ) . '</p></div>';
        }
    }

    $enabled = get_option('tabi_popup_enabled', 0);
    $button  = get_option('tabi_popup_button', 'Click here');
    $button_url = get_option('tabi_popup_button_url', '#');
    $pages   = get_option('tabi_popup_pages', '');
    $bg_color = get_option('tabi_popup_bg_color', '#b8a999');
    $scroll  = get_option('tabi_popup_scroll', 80);
    $delay   = get_option('tabi_popup_delay', 3);
    $trigger = get_option('tabi_popup_trigger_type', 'scroll');
    $position = get_option('tabi_popup_position', 'center');
    $width    = get_option('tabi_popup_width', 'default');

    $headline = get_option('tabi_popup_headline', 'Your Headline Here');
    $headline_font_size = get_option('tabi_popup_headline_font_size', 28);
    $headline_font_size_mobile = get_option('tabi_popup_headline_font_size_mobile', 24);
    $headline_font_family = get_option('tabi_popup_headline_font_family', 'sans-serif');
    $headline_font_weight = get_option('tabi_popup_headline_font_weight', '500');
    $headline_font_color = get_option('tabi_popup_headline_font_color', '#000000');
    $headline_alignment = get_option('tabi_popup_headline_alignment', 'center');
    $headline_alignment_mobile = get_option('tabi_popup_headline_alignment_mobile', 'center');

    $paragraph = get_option('tabi_popup_paragraph', 'This is the main paragraph text for the popup. You can change it in the settings.');
    $paragraph_font_size = get_option('tabi_popup_paragraph_font_size', 16);
    $paragraph_font_size_mobile = get_option('tabi_popup_paragraph_font_size_mobile', 14);
    $paragraph_font_family = get_option('tabi_popup_paragraph_font_family', 'sans-serif');
    $paragraph_font_weight = get_option('tabi_popup_paragraph_font_weight', '400');
    $paragraph_font_color = get_option('tabi_popup_paragraph_font_color', '#333333');
    $paragraph_alignment = get_option('tabi_popup_paragraph_alignment', 'center');
    $paragraph_alignment_mobile = get_option('tabi_popup_paragraph_alignment_mobile', 'center');
    ?>
    <style>
        .tabi-settings-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            padding: 0;
            max-width: 800px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .tabi-section-title {
            font-size: 1.3em;
            margin: 0;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            color: #1d2327;
            background: #fcfcfc;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .tabi-section-title .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
            color: #2271b1;
        }
        .tabi-form-section {
            padding: 20px 30px;
            border-bottom: 1px solid #f0f0f1;
        }
        .tabi-color-picker-wrap {
            padding-top: 5px;
        }
        /* Preview Styles */
        .tabi-preview-container {
            margin-top: 30px;
            padding: 20px;
            background: #f0f0f1;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .tabi-preview-title {
            font-size: 1.2em;
            margin-bottom: 15px;
            font-weight: 600;
        }
        #tabi-popup-preview-wrapper {
            position: relative;
            min-height: 300px;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlNWU1ZTUiIHN0cm9rZS13aWR0aD0iMSI+PHBhdGggZD0iTTAgMGgyMHYyMEgwVjB6Ii8+PC9zdmc+') repeat;
            border: 1px solid #ddd;
            display: flex;
            overflow: hidden;
            transition: width 0.3s ease, border-radius 0.3s ease, height 0.3s ease;
            margin: 0 auto;
            width: 100%;
        }
        .tabi-preview-controls {
            margin-bottom: 15px;
            display: flex;
            gap: 5px;
        }
        .tabi-preview-controls .button.active {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        #tabi-popup-preview-wrapper.mobile-view {
            width: 375px;
            height: 600px;
            border: 8px solid #333;
            border-radius: 24px;
        }
        /* Override popup overlay for preview to stay inside the box */
        #tabi-popup-preview-wrapper .tabi-popup-overlay {
            position: absolute;
            opacity: 1;
            visibility: visible;
            z-index: 10;
        }
        /* Animation styles for preview */
        .tabi-popup-overlay.tabi-popup-center {
            align-items: center;
            justify-content: center;
        }
        .tabi-popup-overlay.tabi-popup-bottom {
            align-items: flex-end;
            justify-content: center;
        }
        .tabi-popup-content {
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform: translateY(20px);
        }
        .tabi-popup-overlay.tabi-popup-bottom .tabi-popup-content {
            margin-bottom: 20px;
            transform: translateY(100%);
        }
        .tabi-popup-overlay.active .tabi-popup-content {
            opacity: 1;
            transform: translateY(0);
        }
        .tabi-popup-overlay.tabi-popup-bottom .tabi-popup-content.tabi-popup-full {
            margin-bottom: 0;
        }
        /* Footer Styles */
        .tabi-admin-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccd0d4;
            text-align: center;
            color: #646970;
            font-size: 13px;
        }
        .tabi-admin-footer a {
            color: #2271b1;
            text-decoration: none;
        }
        .tabi-admin-footer a:hover {
            text-decoration: underline;
        }
        /* New UI Helpers */
        .tabi-header-intro {
            background: #fff;
            padding: 20px;
            border-left: 4px solid #2271b1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .tabi-header-intro h2 { margin-top: 0; }
        .tabi-help-box {
            background: #e5f5fa;
            border: 1px solid #b8e6f5;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 13px;
        }
        .tabi-typography-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            margin-top: 10px;
        }
        .tabi-control-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 12px;
            color: #50575e;
        }
        .tabi-align-controls label {
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
        }
        .tabi-align-controls input[type="radio"] {
            margin-right: 2px;
        }
        .tabi-device-label {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
            color: #2271b1;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
    </style>

    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e( 'Tabi Popup Manager', 'tabi-plugin' ); ?></h1>
        
        <div class="tabi-header-intro">
            <h2><?php esc_html_e( 'Welcome to Tabi Popup Settings', 'tabi-plugin' ); ?></h2>
            <p><?php esc_html_e( 'Create high-converting popups easily. Customize the appearance, content, and behavior to match your site\'s design. Use the preview below to see your changes in real-time.', 'tabi-plugin' ); ?></p>
        </div>

        <form method="post">
            <?php wp_nonce_field('tabi_save_action', 'tabi_popup_nonce'); ?>
            
            <div class="tabi-settings-card">
                <!-- Display Section -->
                <div class="tabi-form-section">
                    <h2 class="tabi-section-title"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Display and Control', 'tabi-plugin' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Enable Popup', 'tabi-plugin' ); ?></th>
                            <td>
                                <label for="popup_enabled">
                                    <input type="checkbox" id="popup_enabled" name="popup_enabled" value="1" <?php checked($enabled, 1); ?>>
                                    <?php esc_html_e( 'Show popup on site', 'tabi-plugin' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Enables or disables the popup display for visitors.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Page IDs (Optional)', 'tabi-plugin' ); ?></th>
                            <td>
                                <input type="text" name="popup_pages" value="<?php echo esc_attr($pages); ?>" class="regular-text" placeholder="Ex: 10, 25">
                                <p class="description"><?php _e( 'Enter page IDs separated by commas (e.g., 10, 25). Leave blank to show on <strong>all pages</strong>.', 'tabi-plugin' ); ?></p>
                                <div class="tabi-help-box">
                                    <strong><?php esc_html_e( 'How to find Page ID:', 'tabi-plugin' ); ?></strong>
                                    <ol style="margin: 5px 0 0 20px; list-style: decimal;">
                                        <li><?php _e( 'Go to your WordPress Dashboard > <strong>Pages</strong>.', 'tabi-plugin' ); ?></li>
                                        <li><?php esc_html_e( 'Hover over the page you want to target.', 'tabi-plugin' ); ?></li>
                                        <li><?php _e( 'Look at the URL at the bottom of your browser. The number after <code>post=</code> is your ID.', 'tabi-plugin' ); ?></li>
                                    </ol>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Popup Position', 'tabi-plugin' ); ?></th>
                            <td>
                                <fieldset>
                                    <label><input type="radio" name="popup_position" value="center" <?php checked($position, 'center'); ?>> <?php esc_html_e( 'Center of Screen', 'tabi-plugin' ); ?></label><br>
                                    <label><input type="radio" name="popup_position" value="bottom" <?php checked($position, 'bottom'); ?>> <?php esc_html_e( 'Bottom of Screen', 'tabi-plugin' ); ?></label>
                                </fieldset>
                                <p class="description"><?php esc_html_e( 'Choose where the popup should appear.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Popup Width', 'tabi-plugin' ); ?></th>
                            <td>
                                <fieldset>
                                    <label><input type="radio" name="popup_width" value="default" <?php checked($width, 'default'); ?>> <?php esc_html_e( 'Default (Compact)', 'tabi-plugin' ); ?></label><br>
                                    <label><input type="radio" name="popup_width" value="full" <?php checked($width, 'full'); ?>> <?php esc_html_e( 'Full Width (100%)', 'tabi-plugin' ); ?></label>
                                </fieldset>
                                <p class="description"><?php esc_html_e( 'Choose if the popup should be compact or span the full width of the screen.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Trigger Type', 'tabi-plugin' ); ?></th>
                            <td>
                                <fieldset>
                                    <label><input type="radio" name="popup_trigger_type" value="scroll" <?php checked($trigger, 'scroll'); ?>> <?php esc_html_e( 'Scroll Percentage', 'tabi-plugin' ); ?></label><br>
                                    <label><input type="radio" name="popup_trigger_type" value="delay" <?php checked($trigger, 'delay'); ?>> <?php esc_html_e( 'Time Delay', 'tabi-plugin' ); ?></label>
                                </fieldset>
                                <p class="description"><?php esc_html_e( 'Choose how the popup should be triggered.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr id="tabi-scroll-trigger-row">
                            <th scope="row"><?php esc_html_e( 'Scroll Trigger (%)', 'tabi-plugin' ); ?></th>
                            <td>
                                <input type="number" name="popup_scroll" value="<?php echo esc_attr($scroll); ?>" class="small-text" min="0" max="100">
                                <p class="description"><?php _e( 'Used if Trigger Type is set to <strong>Scroll Percentage</strong>.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr id="tabi-delay-trigger-row">
                            <th scope="row"><?php esc_html_e( 'Delay (seconds)', 'tabi-plugin' ); ?></th>
                            <td>
                                <input type="number" name="popup_delay" value="<?php echo esc_attr($delay); ?>" class="small-text" min="0">
                                <p class="description"><?php _e( 'Used if Trigger Type is set to <strong>Time Delay</strong>.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Content Section -->
                <div class="tabi-form-section">
                    <h2 class="tabi-section-title"><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Content and Style', 'tabi-plugin' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr class="tabi-sub-heading"><th colspan="2"><h3><?php esc_html_e( 'Headline (H2)', 'tabi-plugin' ); ?></h3></th></tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Headline Text', 'tabi-plugin' ); ?></th>
                            <td>
                                <input type="text" name="popup_headline" value="<?php echo esc_attr($headline); ?>" class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Headline Style', 'tabi-plugin' ); ?></th>
                            <td>
                                <div class="tabi-typography-grid">
                                    <!-- Desktop Settings -->
                                    <div class="tabi-control-group" style="grid-column: 1 / -1;">
                                        <div class="tabi-device-label"><span class="dashicons dashicons-desktop"></span> <?php esc_html_e( 'Desktop Settings', 'tabi-plugin' ); ?></div>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Font Family', 'tabi-plugin' ); ?></label>
                                        <select name="popup_headline_font_family" style="width: 100%;">
                                            <option value="sans-serif" <?php selected($headline_font_family, 'sans-serif'); ?>><?php esc_html_e( 'System Default', 'tabi-plugin' ); ?></option>
                                            <option value="serif" <?php selected($headline_font_family, 'serif'); ?>><?php esc_html_e( 'Serif', 'tabi-plugin' ); ?></option>
                                            <option value="'futura-pt', sans-serif" <?php selected($headline_font_family, "'futura-pt', sans-serif"); ?>><?php esc_html_e( 'Futura PT', 'tabi-plugin' ); ?></option>
                                            <option value="Arial, Helvetica, sans-serif" <?php selected($headline_font_family, 'Arial, Helvetica, sans-serif'); ?>><?php esc_html_e( 'Arial', 'tabi-plugin' ); ?></option>
                                            <option value="'Times New Roman', Times, serif" <?php selected($headline_font_family, "'Times New Roman', Times, serif"); ?>><?php esc_html_e( 'Times New Roman', 'tabi-plugin' ); ?></option>
                                        </select>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Font Weight', 'tabi-plugin' ); ?></label>
                                        <select name="popup_headline_font_weight" style="width: 100%;">
                                            <?php foreach([300, 400, 500, 600, 700, 800, 900] as $w): ?>
                                                <option value="<?php echo $w; ?>" <?php selected($headline_font_weight, $w); ?>><?php echo $w; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Size (px)', 'tabi-plugin' ); ?></label>
                                        <input type="number" name="popup_headline_font_size" value="<?php echo esc_attr($headline_font_size); ?>" class="small-text" min="1">
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Color', 'tabi-plugin' ); ?></label>
                                        <input type="text" name="popup_headline_font_color" value="<?php echo esc_attr($headline_font_color); ?>" class="tabi-color-picker">
                                    </div>
                                    <div class="tabi-control-group" style="grid-column: 1 / -1;">
                                        <label><?php esc_html_e( 'Alignment', 'tabi-plugin' ); ?></label>
                                        <div class="tabi-align-controls">
                                            <label><input type="radio" name="popup_headline_alignment" value="left" <?php checked($headline_alignment, 'left'); ?>> <span class="dashicons dashicons-editor-alignleft"></span></label>
                                            <label><input type="radio" name="popup_headline_alignment" value="center" <?php checked($headline_alignment, 'center'); ?>> <span class="dashicons dashicons-editor-aligncenter"></span></label>
                                            <label><input type="radio" name="popup_headline_alignment" value="right" <?php checked($headline_alignment, 'right'); ?>> <span class="dashicons dashicons-editor-alignright"></span></label>
                                        </div>
                                    </div>

                                    <!-- Mobile Settings -->
                                    <div class="tabi-control-group" style="grid-column: 1 / -1; margin-top: 10px;">
                                        <div class="tabi-device-label"><span class="dashicons dashicons-smartphone"></span> <?php esc_html_e( 'Mobile Settings', 'tabi-plugin' ); ?></div>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Mobile Size (px)', 'tabi-plugin' ); ?></label>
                                        <input type="number" name="popup_headline_font_size_mobile" value="<?php echo esc_attr($headline_font_size_mobile); ?>" class="small-text" min="1">
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Mobile Alignment', 'tabi-plugin' ); ?></label>
                                        <div class="tabi-align-controls">
                                            <label><input type="radio" name="popup_headline_alignment_mobile" value="left" <?php checked($headline_alignment_mobile, 'left'); ?>> <span class="dashicons dashicons-editor-alignleft"></span></label>
                                            <label><input type="radio" name="popup_headline_alignment_mobile" value="center" <?php checked($headline_alignment_mobile, 'center'); ?>> <span class="dashicons dashicons-editor-aligncenter"></span></label>
                                            <label><input type="radio" name="popup_headline_alignment_mobile" value="right" <?php checked($headline_alignment_mobile, 'right'); ?>> <span class="dashicons dashicons-editor-alignright"></span></label>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="tabi-sub-heading"><th colspan="2"><h3 style="margin-top:20px;"><?php esc_html_e( 'Paragraph', 'tabi-plugin' ); ?></h3></th></tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Paragraph Text', 'tabi-plugin' ); ?></th>
                            <td>
                                <textarea name="popup_paragraph" class="large-text" rows="4"><?php echo esc_textarea($paragraph); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Paragraph Style', 'tabi-plugin' ); ?></th>
                            <td>
                                <div class="tabi-typography-grid">
                                    <!-- Desktop Settings -->
                                    <div class="tabi-control-group" style="grid-column: 1 / -1;">
                                        <div class="tabi-device-label"><span class="dashicons dashicons-desktop"></span> <?php esc_html_e( 'Desktop Settings', 'tabi-plugin' ); ?></div>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Font Family', 'tabi-plugin' ); ?></label>
                                        <select name="popup_paragraph_font_family" style="width: 100%;">
                                            <option value="sans-serif" <?php selected($paragraph_font_family, 'sans-serif'); ?>><?php esc_html_e( 'System Default', 'tabi-plugin' ); ?></option>
                                            <option value="serif" <?php selected($paragraph_font_family, 'serif'); ?>><?php esc_html_e( 'Serif', 'tabi-plugin' ); ?></option>
                                            <option value="'futura-pt', sans-serif" <?php selected($paragraph_font_family, "'futura-pt', sans-serif"); ?>><?php esc_html_e( 'Futura PT', 'tabi-plugin' ); ?></option>
                                            <option value="Arial, Helvetica, sans-serif" <?php selected($paragraph_font_family, 'Arial, Helvetica, sans-serif'); ?>><?php esc_html_e( 'Arial', 'tabi-plugin' ); ?></option>
                                            <option value="'Times New Roman', Times, serif" <?php selected($paragraph_font_family, "'Times New Roman', Times, serif"); ?>><?php esc_html_e( 'Times New Roman', 'tabi-plugin' ); ?></option>
                                        </select>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Font Weight', 'tabi-plugin' ); ?></label>
                                        <select name="popup_paragraph_font_weight" style="width: 100%;">
                                            <?php foreach([300, 400, 500, 600, 700, 800, 900] as $w): ?>
                                                <option value="<?php echo $w; ?>" <?php selected($paragraph_font_weight, $w); ?>><?php echo $w; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Size (px)', 'tabi-plugin' ); ?></label>
                                        <input type="number" name="popup_paragraph_font_size" value="<?php echo esc_attr($paragraph_font_size); ?>" class="small-text" min="1">
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Color', 'tabi-plugin' ); ?></label>
                                        <input type="text" name="popup_paragraph_font_color" value="<?php echo esc_attr($paragraph_font_color); ?>" class="tabi-color-picker">
                                    </div>
                                    <div class="tabi-control-group" style="grid-column: 1 / -1;">
                                        <label><?php esc_html_e( 'Alignment', 'tabi-plugin' ); ?></label>
                                        <div class="tabi-align-controls">
                                            <label><input type="radio" name="popup_paragraph_alignment" value="left" <?php checked($paragraph_alignment, 'left'); ?>> <span class="dashicons dashicons-editor-alignleft"></span></label>
                                            <label><input type="radio" name="popup_paragraph_alignment" value="center" <?php checked($paragraph_alignment, 'center'); ?>> <span class="dashicons dashicons-editor-aligncenter"></span></label>
                                            <label><input type="radio" name="popup_paragraph_alignment" value="right" <?php checked($paragraph_alignment, 'right'); ?>> <span class="dashicons dashicons-editor-alignright"></span></label>
                                        </div>
                                    </div>

                                    <!-- Mobile Settings -->
                                    <div class="tabi-control-group" style="grid-column: 1 / -1; margin-top: 10px;">
                                        <div class="tabi-device-label"><span class="dashicons dashicons-smartphone"></span> <?php esc_html_e( 'Mobile Settings', 'tabi-plugin' ); ?></div>
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Mobile Size (px)', 'tabi-plugin' ); ?></label>
                                        <input type="number" name="popup_paragraph_font_size_mobile" value="<?php echo esc_attr($paragraph_font_size_mobile); ?>" class="small-text" min="1">
                                    </div>
                                    <div class="tabi-control-group">
                                        <label><?php esc_html_e( 'Mobile Alignment', 'tabi-plugin' ); ?></label>
                                        <div class="tabi-align-controls">
                                            <label><input type="radio" name="popup_paragraph_alignment_mobile" value="left" <?php checked($paragraph_alignment_mobile, 'left'); ?>> <span class="dashicons dashicons-editor-alignleft"></span></label>
                                            <label><input type="radio" name="popup_paragraph_alignment_mobile" value="center" <?php checked($paragraph_alignment_mobile, 'center'); ?>> <span class="dashicons dashicons-editor-aligncenter"></span></label>
                                            <label><input type="radio" name="popup_paragraph_alignment_mobile" value="right" <?php checked($paragraph_alignment_mobile, 'right'); ?>> <span class="dashicons dashicons-editor-alignright"></span></label>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php esc_html_e( 'Action Button', 'tabi-plugin' ); ?></th>
                            <td>
                                <input type="text" name="popup_button" value="<?php echo esc_attr($button); ?>" class="regular-text" placeholder="Button text">
                                <br><br>
                                <input type="url" name="popup_button_url" value="<?php echo esc_attr($button_url); ?>" class="regular-text" placeholder="https://...">
                                <p class="description"><?php esc_html_e( 'Define the button label (e.g., "Learn More") and the destination URL.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Background Color', 'tabi-plugin' ); ?></th>
                            <td class="tabi-color-picker-wrap">
                                <input type="text" name="popup_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="tabi-color-picker">
                                <p class="description"><?php esc_html_e( 'Choose the background color for the popup window.', 'tabi-plugin' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Preview Section -->
            <div class="tabi-preview-container">
                <div class="tabi-preview-title"><?php esc_html_e( 'Live Preview', 'tabi-plugin' ); ?></div>
                <div class="tabi-preview-controls">
                    <button type="button" class="button active" id="tabi-preview-desktop"><span class="dashicons dashicons-desktop"></span> <?php esc_html_e( 'Desktop', 'tabi-plugin' ); ?></button>
                    <button type="button" class="button" id="tabi-preview-mobile"><span class="dashicons dashicons-smartphone"></span> <?php esc_html_e( 'Mobile', 'tabi-plugin' ); ?></button>
                </div>
                <div id="tabi-popup-preview-wrapper">
                    <div id="preview-overlay" class="tabi-popup-overlay tabi-popup-center">
                        <div id="preview-content" class="tabi-popup-content" style="background-color: <?php echo esc_attr($bg_color); ?>;">
                            <span class="tabi-popup-close">&times;</span>
                            <div class="tabi-popup-body">
                                <div id="preview-text-container"></div>
                                <a href="#" id="preview-button" class="tabi-popup-button" onclick="return false;"><?php echo esc_html($button); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="submit" style="padding-left: 0;">
                <input type="submit" name="tabi_save" class="button button-primary button-large" value="<?php esc_attr_e( 'Save Changes', 'tabi-plugin' ); ?>">
            </p>
        </form>

        <div class="tabi-admin-footer">
            <p><?php _e( 'Developed by <a href="#" target="_blank">Tabi</a> & <a href="#" target="_blank">Franklyn Agency</a>.', 'tabi-plugin' ); ?></p>
        </div>
    </div>
    <?php
}

/* --- View Tracking & Dashboard Widget --- */

function tabi_track_popup_view() {
    check_ajax_referer('tabi_track_view_nonce', 'nonce');
    
    $views = get_option('tabi_popup_views', 0);
    $views++;
    update_option('tabi_popup_views', $views);
    
    wp_send_json_success();
}
add_action('wp_ajax_tabi_track_view', 'tabi_track_popup_view');
add_action('wp_ajax_nopriv_tabi_track_view', 'tabi_track_popup_view');

function tabi_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'tabi_popup_views_widget',
        __( 'Tabi Popup Views', 'tabi-plugin' ),
        'tabi_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'tabi_add_dashboard_widgets');

function tabi_dashboard_widget_content() {
    $views = get_option('tabi_popup_views', 0);
    echo '<div class="tabi-dashboard-widget" style="text-align: center; padding: 20px;">';
    echo '<div class="dashicons dashicons-visibility" style="font-size: 40px; width: 40px; height: 40px; color: #2271b1; margin-bottom: 10px;"></div>';
    echo '<h3 style="font-size: 36px; margin: 0; color: #1d2327; line-height: 1;">' . number_format($views) . '</h3>';
    echo '<p style="margin-top: 10px; color: #646970; font-size: 14px;">' . esc_html__( 'Total Popup Impressions', 'tabi-plugin' ) . '</p>';
    echo '</div>';
}
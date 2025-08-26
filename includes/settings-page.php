<?php
/**
 * Handles the plugin's settings page.
 *
 * @package HomepageStickySlider
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Adds a new submenu page under the "Settings" menu.
 */
function hss_add_settings_page() {
    add_options_page(
        'Homepage Slider Settings', // Page title
        'Homepage Slider',          // Menu title
        'manage_options',           // Capability required
        'hss-settings',             // Menu slug
        'hss_render_settings_page'  // Callback function to render the page
    );
}
add_action( 'admin_menu', 'hss_add_settings_page' );

/**
 * Renders the HTML for the settings page.
 */
function hss_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Homepage Slider Settings</h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting section.
            settings_fields( 'hss_settings_group' );
            // Output the fields for the section.
            do_settings_sections( 'hss-settings' );
            // Output the submit button.
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registers the plugin's settings, section, and fields.
 */
function hss_register_settings() {
    // Register settings.
    register_setting( 'hss_settings_group', 'hss_slider_post_count', 'absint' );
    register_setting( 'hss_settings_group', 'hss_slider_hook_name', 'sanitize_text_field' );
    register_setting( 'hss_settings_group', 'hss_slider_margin_top', 'absint' );
    register_setting( 'hss_settings_group', 'hss_slider_margin_bottom', 'absint' );
    register_setting( 'hss_settings_group', 'hss_click_behavior', 'sanitize_text_field' );
    register_setting( 'hss_settings_group', 'hss_enable_autoplay', 'sanitize_text_field' );
    register_setting( 'hss_settings_group', 'hss_autoplay_delay', 'absint' );

    // Add a settings section.
    add_settings_section( 'hss_slider_settings_section', 'Slider Configuration', null, 'hss-settings' );

    // Add settings fields.
    add_settings_field( 'hss_slider_post_count_field', 'Number of Posts to Show', 'hss_post_count_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_slider_hook_name_field', 'Theme Hook Name', 'hss_hook_name_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_slider_margin_top_field', 'Margin Top (px)', 'hss_margin_top_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_slider_margin_bottom_field', 'Margin Bottom (px)', 'hss_margin_bottom_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_click_behavior_field', 'Click Behavior', 'hss_click_behavior_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_enable_autoplay_field', 'Enable Autoplay', 'hss_enable_autoplay_field_callback', 'hss-settings', 'hss_slider_settings_section' );
    add_settings_field( 'hss_autoplay_delay_field', 'Autoplay Delay (ms)', 'hss_autoplay_delay_field_callback', 'hss-settings', 'hss_slider_settings_section' );
}
add_action( 'admin_init', 'hss_register_settings' );

// Callback functions for rendering fields...
function hss_post_count_field_callback() {
    $setting = get_option( 'hss_slider_post_count', 5 );
    echo '<input type="number" name="hss_slider_post_count" value="' . esc_attr( $setting ) . '" min="1" max="20" />';
    echo '<p class="description">Enter the maximum number of posts to display in the slider (e.g., 5).</p>';
}

function hss_hook_name_field_callback() {
    $setting = get_option( 'hss_slider_hook_name', 'generate_after_header' );
    echo '<input type="text" name="hss_slider_hook_name" value="' . esc_attr( $setting ) . '" class="regular-text" />';
    echo '<p class="description">Enter the theme action hook to display the slider. Example: <code>generate_after_header</code>.</p>';
}

function hss_margin_top_field_callback() {
    $setting = get_option( 'hss_slider_margin_top', 20 );
    echo '<input type="number" name="hss_slider_margin_top" value="' . esc_attr( $setting ) . '" min="0" />';
}

function hss_margin_bottom_field_callback() {
    $setting = get_option( 'hss_slider_margin_bottom', 20 );
    echo '<input type="number" name="hss_slider_margin_bottom" value="' . esc_attr( $setting ) . '" min="0" />';
}

function hss_click_behavior_field_callback() {
    $setting = get_option( 'hss_click_behavior', 'entire_slide' );
    ?>
    <select name="hss_click_behavior">
        <option value="entire_slide" <?php selected( $setting, 'entire_slide' ); ?>>Entire Slide</option>
        <option value="title_only" <?php selected( $setting, 'title_only' ); ?>>Title Only</option>
        <option value="read_more" <?php selected( $setting, 'read_more' ); ?>>"Read More" Button</option>
    </select>
    <p class="description">Choose which part of the slide should be clickable.</p>
    <?php
}

function hss_enable_autoplay_field_callback() {
    $setting = get_option( 'hss_enable_autoplay', 'on' );
    echo '<input type="checkbox" name="hss_enable_autoplay" ' . checked( $setting, 'on', false ) . ' />';
}

function hss_autoplay_delay_field_callback() {
    $setting = get_option( 'hss_autoplay_delay', 5000 );
    echo '<input type="number" name="hss_autoplay_delay" value="' . esc_attr( $setting ) . '" min="1000" step="500" />';
    echo '<p class="description">Time between slides in milliseconds (e.g., 5000 = 5 seconds).</p>';
}

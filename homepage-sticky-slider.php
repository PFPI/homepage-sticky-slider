<?php
/**
 * Plugin Name:       Homepage Sticky Post Slider
 * Plugin URI:        https://example.com/
 * Description:       Adds a checkbox to posts to feature them in a homepage slider.
 * Version:           1.1.0
 * Author:            Your Name Here
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       homepage-sticky-slider
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define plugin constants
 */
define( 'HSS_VERSION', '1.1.0' );
define( 'HSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


/**
 * Include the plugin's files.
 * The code for the admin meta box and the frontend slider will live in these files.
 */
require_once HSS_PLUGIN_DIR . 'includes/post-meta.php';
require_once HSS_PLUGIN_DIR . 'includes/frontend-slider.php';
require_once HSS_PLUGIN_DIR . 'includes/settings-page.php'; // Add this line

<?php
/**
 * WP Page Fold Hyperlinks Tracking
 *
 * @package   wp-page-fold-hyperlinks-tracking
 * @author    Sopheak DY
 * @copyright Sopheak DY
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: WP Page Fold Hyperlinks Tracking
 * Version:     1.0.0
 * Description: Tracks which hyperlinks are seen above the fold on the homepage for the past 7 days.
 * Author:      Sopheak DY
 */

namespace ROCKET_WP_CRAWLER;

define( 'ROCKET_CRWL_PLUGIN_FILENAME', __FILE__ ); // Filename of the plugin, including the file.

if ( ! defined( 'ABSPATH' ) ) { // If WordPress is not loaded.
	exit( 'WordPress not loaded. Can not load the plugin' );
}

// Load the dependencies installed through composer.
require_once __DIR__ . '/src/plugin.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/support/exceptions.php';
require_once __DIR__ . '/src/rest-endpoints.php';
require_once __DIR__ . '/src/admin-page.php';

// Plugin initialization.
/**
 * Creates the plugin object on plugins_loaded hook
 *
 * @return void
 */
function wpc_crawler_plugin_init() {
	$wpc_crawler_plugin = new Rocket_Wpc_Plugin_Class();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wpc_crawler_plugin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_activate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_uninstall' );

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_front_page() || is_home() ) {
			$js_path = plugin_dir_path( __FILE__ ) . 'assets/js/above-the-fold-tracker.js';
			$ver     = file_exists( $js_path ) ? filemtime( $js_path ) : false;
			wp_enqueue_script(
				'above-the-fold-tracker',
				plugins_url( 'assets/js/above-the-fold-tracker.js', __FILE__ ),
				array(),
				$ver,
				true
			);
			wp_localize_script(
				'above-the-fold-tracker',
				'WPPageFoldTracker',
				array(
					'restUrl' => rest_url( 'wp-page-fold/v1/track' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}
);

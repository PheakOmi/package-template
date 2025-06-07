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

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'wp-page-fold/v1',
			'/track',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$nonce = $request->get_header( 'x_wp_nonce' );
					if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
						return new WP_Error( 'rest_forbidden', 'Invalid nonce', array( 'status' => 403 ) );
					}
					$params = $request->get_json_params();
					global $wpdb;
					$table = $wpdb->prefix . 'page_fold_visits';
					$wpdb->insert(
						$table,
						array(
							'visit_time'    => current_time( 'mysql' ),
							'screen_width'  => isset( $params['screen']['width'] ) ? intval( $params['screen']['width'] ) : 0,
							'screen_height' => isset( $params['screen']['height'] ) ? intval( $params['screen']['height'] ) : 0,
							'hrefs'         => isset( $params['hrefs'] ) ? wp_json_encode( $params['hrefs'] ) : '[]',
						)
					);
					return rest_ensure_response( array( 'received' => $params ) );
				},
				'permission_callback' => '__return_true',
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_management_page(
			'Page Fold Hyperlinks Tracking',
			'Page Fold Tracking',
			'manage_options',
			'page-fold-tracking',
			function () {
				global $wpdb;
				$table = $wpdb->prefix . 'page_fold_visits';
				$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY visit_time DESC LIMIT 50" );
				echo '<div class="wrap"><h1>Page Fold Hyperlinks Tracking</h1>';
				if ( ! $rows ) {
					echo '<p>No data found.</p>';
				} else {
					echo '<table class="widefat"><thead><tr><th>Time</th><th>Screen</th><th>Links</th></tr></thead><tbody>';
					foreach ( $rows as $row ) {
						$hrefs = json_decode( $row->hrefs, true );
						echo '<tr>';
						echo '<td>' . esc_html( $row->visit_time ) . '</td>';
						echo '<td>' . esc_html( $row->screen_width . 'x' . $row->screen_height ) . '</td>';
						echo '<td>';
						if ( $hrefs && is_array( $hrefs ) ) {
							foreach ( $hrefs as $href ) {
								echo '<div>' . esc_html( $href ) . '</div>';
							}
						}
						echo '</td>';
						echo '</tr>';
					}
					echo '</tbody></table>';
				}
				echo '</div>';
			}
		);
	}
);

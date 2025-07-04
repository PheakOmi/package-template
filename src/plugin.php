<?php
/**
 * Plugin main class
 *
 * @package wp-page-fold-hyperlinks-tracking
 * @since   1.0.0
 * @author  Sopheak DY
 * @license GPL-2.0-or-later
 */

namespace ROCKET_WP_CRAWLER;

/**
 * Main plugin class. It manages initialization, install, and activations.
 */
class Rocket_Wpc_Plugin_Class {

	/**
	 * Manages plugin initialization
	 *
	 * @return void
	 */
	public function __construct() {

		// Register plugin lifecycle hooks.
		register_deactivation_hook( ROCKET_CRWL_PLUGIN_FILENAME, array( $this, 'wpc_deactivate' ) );
	}

	/**
	 * Handles plugin activation:
	 *
	 * @return void
	 */
	public static function wpc_activate() {
		global $wpdb;
		$table           = $wpdb->prefix . 'page_fold_visits';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE IF NOT EXISTS $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			visit_time DATETIME NOT NULL,
			screen_width INT NOT NULL,
			screen_height INT NOT NULL,
			hrefs TEXT NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		if ( ! wp_next_scheduled( 'wp_page_fold_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wp_page_fold_cleanup' );
		}
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "activate-plugin_{$plugin}" );
	}

	/**
	 * Handles plugin deactivation
	 *
	 * @return void
	 */
	public function wpc_deactivate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
		wp_clear_scheduled_hook( 'wp_page_fold_cleanup' );
	}

	/**
	 * Handles plugin uninstall
	 *
	 * @return void
	 */
	public static function wpc_uninstall() {
		global $wpdb;
		$table = $wpdb->prefix . 'page_fold_visits';
     // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}
}

add_action(
	'wp_page_fold_cleanup',
	function () {
		global $wpdb;
		$table = $wpdb->prefix . 'page_fold_visits';
     // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$query = $wpdb->prepare(
			"DELETE FROM {$table} WHERE visit_time < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}
);

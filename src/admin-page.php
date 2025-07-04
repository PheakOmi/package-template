<?php
namespace ROCKET_WP_CRAWLER;

add_action(
	'admin_menu',
	function () {
		add_management_page(
			'Page Fold Hyperlinks Tracking',
			'Page Fold Tracking',
			'manage_options',
			'page-fold-tracking',
			__NAMESPACE__ . '\\wpc_render_admin_page'
		);
	}
);

/**
 * Renders the admin page for Page Fold Hyperlinks Tracking.
 */
function wpc_render_admin_page() {
	global $wpdb;
	$table = $wpdb->prefix . 'page_fold_visits';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
	$query = $wpdb->prepare( "SELECT * FROM {$table} ORDER BY visit_time DESC LIMIT %d", 50 );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query
	);
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
					echo '<div>' . esc_url( $href ) . '</div>';
				}
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}

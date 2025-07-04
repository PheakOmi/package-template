<?php
namespace ROCKET_WP_CRAWLER;

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'wp-page-fold/v1',
			'/track',
			array(
				'methods'             => 'POST',
				'callback'            => __NAMESPACE__ . '\\wpc_page_fold_track_callback',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * Callback for the page fold tracking REST endpoint.
 *
 * @param \WP_REST_Request $request The REST request object.
 * @return \WP_REST_Response|\WP_Error
 */
function wpc_page_fold_track_callback( $request ) {
	$nonce = $request->get_header( 'x_wp_nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new \WP_Error( 'rest_forbidden', 'Invalid nonce', array( 'status' => 403 ) );
	}
	$params = $request->get_json_params();
	global $wpdb;
	$table = $wpdb->prefix . 'page_fold_visits';
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'visit_time'    => current_time( 'mysql' ),
			'screen_width'  => isset( $params['screen']['width'] ) ? intval( $params['screen']['width'] ) : 0,
			'screen_height' => isset( $params['screen']['height'] ) ? intval( $params['screen']['height'] ) : 0,
			'hrefs'         => isset( $params['hrefs'] ) ? wp_json_encode( $params['hrefs'] ) : '[]',
		)
	);
	return rest_ensure_response( array( 'received' => $params ) );
}

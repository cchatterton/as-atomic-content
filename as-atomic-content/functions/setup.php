<?php
/**
 * ACF definitions and compatibility with existing ACF post type records.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the plugin's ACF Local JSON directory without removing other load paths.
 *
 * @param array $paths Existing ACF JSON paths.
 * @return array
 */
function asac_acf_json_load_paths( $paths ) {
	$paths[] = ASAC_PLUGIN_DIR . 'acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'asac_acf_json_load_paths' );

/** Whether the installed ACF version supports post types in Local JSON. */
function asac_acf_available() {
	return function_exists( 'acf' ) && defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '6.1', '>=' );
}

/**
 * Keep old ACF database definitions under the same menu after installation.
 *
 * @param array $args      WordPress registration arguments.
 * @param array $post_type ACF post type definition.
 * @return array
 */
function asac_acf_post_type_args( $args, $post_type ) {
	if ( ! isset( $post_type['post_type'] ) || ! in_array( $post_type['post_type'], array( 'card', 'row', 'pattern' ), true ) ) {
		return $args;
	}

	$args['show_in_menu'] = false;
	$args['show_in_admin_bar'] = false;
	if ( 'pattern' === $post_type['post_type'] ) {
		$args['show_ui'] = false;
		$args['show_in_rest'] = false;
		$args['public'] = false;
		$args['publicly_queryable'] = true;
		$args['exclude_from_search'] = true;
		$args['show_in_nav_menus'] = false;
		$args['has_archive'] = false;
	}
	return $args;
}
add_filter( 'acf/post_type/registration_args', 'asac_acf_post_type_args', 10, 2 );

/** Refresh permalinks once when pattern viewing is enabled. */
function asac_refresh_pattern_rewrites() {
	if ( ! post_type_exists( 'pattern' ) || '0.1.2' === get_option( 'asac_pattern_rewrite_version' ) ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'asac_pattern_rewrite_version', '0.1.2', false );
}
add_action( 'init', 'asac_refresh_pattern_rewrites', 100 );

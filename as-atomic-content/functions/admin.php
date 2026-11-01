<?php
/**
 * WordPress admin navigation for atomic content.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Register one top-level menu with three post list links. */
function asac_register_admin_menu() {
	if ( ! asac_acf_available() ) {
		return;
	}

	$parent = 'as-atomic-content';
	$hook = add_menu_page(
		__( 'Atomic Content', 'as-atomic-content' ),
		__( 'Atomic Content', 'as-atomic-content' ),
		'edit_posts',
		$parent,
		'__return_null',
		'dashicons-layout',
		80
	);
	if ( $hook ) {
		add_action( 'load-' . $hook, 'asac_redirect_to_cards' );
	}

	foreach ( array(
		'card'    => __( 'Cards', 'as-atomic-content' ),
		'row'     => __( 'Rows', 'as-atomic-content' ),
		'pattern' => __( 'Patterns', 'as-atomic-content' ),
	) as $post_type => $label ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}

		add_submenu_page(
			$parent,
			$label,
			$label,
			'edit_posts',
			'edit.php?post_type=' . $post_type
		);
	}

	// WordPress inserts a parent link automatically; keep only the three lists.
	remove_submenu_page( $parent, $parent );
}
add_action( 'admin_menu', 'asac_register_admin_menu', 20 );

/** Keep the parent menu selected on the three native post screens. */
function asac_admin_parent_file( $parent_file ) {
	$screen = get_current_screen();
	if ( $screen && in_array( $screen->post_type, array( 'card', 'row', 'pattern' ), true ) ) {
		return 'as-atomic-content';
	}
	return $parent_file;
}
add_filter( 'parent_file', 'asac_admin_parent_file' );

/** Highlight the matching list entry while editing an atomic post. */
function asac_admin_submenu_file( $submenu_file ) {
	$screen = get_current_screen();
	if ( $screen && in_array( $screen->post_type, array( 'card', 'row', 'pattern' ), true ) ) {
		return 'edit.php?post_type=' . $screen->post_type;
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'asac_admin_submenu_file' );

/** Send direct visits to the parent menu to the Cards list. */
function asac_redirect_to_cards() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You cannot access atomic content.', 'as-atomic-content' ) );
	}

	wp_safe_redirect( admin_url( 'edit.php?post_type=card' ) );
	exit;
}

/** Explain the required ACF installation to administrators. */
function asac_acf_required_notice() {
	if ( asac_acf_available() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>' . esc_html__( 'AS Atomic Content requires Advanced Custom Fields 6.1 or later to register Cards, Rows, and Patterns.', 'as-atomic-content' ) . '</p></div>';
}
add_action( 'admin_notices', 'asac_acf_required_notice' );

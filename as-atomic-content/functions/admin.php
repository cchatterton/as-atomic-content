<?php
/**
 * WordPress admin navigation for atomic content.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Register Cards, Rows, and the native pattern manager under Atoms. */
function asac_register_admin_menu() {
	if ( ! current_user_can( 'edit_posts' ) || ( ! asac_acf_available() && ! current_user_can( 'edit_theme_options' ) ) ) {
		return;
	}

	$parent = 'as-atomic-content';
	$hook = add_menu_page(
		__( 'Atoms', 'as-atomic-content' ),
		__( 'Atoms', 'as-atomic-content' ),
		'edit_posts',
		$parent,
		'__return_null',
		ASAC_PLUGIN_URL . 'assets/atom.svg',
		80
	);
	if ( $hook ) {
		add_action( 'load-' . $hook, 'asac_redirect_to_cards' );
	}

	foreach ( array(
		'card'    => __( 'Cards', 'as-atomic-content' ),
		'row'     => __( 'Rows', 'as-atomic-content' ),
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

	add_submenu_page(
		$parent,
		__( 'Patterns', 'as-atomic-content' ),
		__( 'Patterns', 'as-atomic-content' ),
		'edit_theme_options',
		'site-editor.php?p=%2Fpattern&postType=wp_block'
	);

	$pattern_taxonomy = get_taxonomy( 'wp_pattern_category' );
	if ( $pattern_taxonomy && current_user_can( $pattern_taxonomy->cap->manage_terms ) ) {
		add_submenu_page(
			$parent,
			__( 'Pattern Categories', 'as-atomic-content' ),
			__( 'Pattern Categories', 'as-atomic-content' ),
			$pattern_taxonomy->cap->manage_terms,
			'edit-tags.php?taxonomy=wp_pattern_category&post_type=wp_block'
		);
	}

	// WordPress inserts a parent link automatically; keep only the content links.
	remove_submenu_page( $parent, $parent );
}
add_action( 'admin_menu', 'asac_register_admin_menu', 20 );

/** Keep Atoms selected on its post and taxonomy screens. */
function asac_admin_parent_file( $parent_file ) {
	$screen = get_current_screen();
	if ( $screen && ( in_array( $screen->post_type, array( 'card', 'row' ), true ) || 'wp_pattern_category' === $screen->taxonomy ) ) {
		return 'as-atomic-content';
	}
	return $parent_file;
}
add_filter( 'parent_file', 'asac_admin_parent_file' );

/** Highlight the matching list entry while editing an atomic post. */
function asac_admin_submenu_file( $submenu_file ) {
	$screen = get_current_screen();
	if ( $screen && in_array( $screen->post_type, array( 'card', 'row' ), true ) ) {
		return 'edit.php?post_type=' . $screen->post_type;
	}
	if ( $screen && 'wp_pattern_category' === $screen->taxonomy ) {
		return 'edit-tags.php?taxonomy=wp_pattern_category&post_type=wp_block';
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'asac_admin_submenu_file' );

/** Whether this is an Atoms admin screen. */
function asac_is_atomic_screen() {
	$screen = get_current_screen();
	return $screen && ( in_array( $screen->post_type, array( 'card', 'row' ), true ) || 'wp_pattern_category' === $screen->taxonomy );
}

/** Scope the sidebar scroll treatment to atomic content screens. */
function asac_admin_body_class( $classes ) {
	if ( asac_is_atomic_screen() ) {
		$classes .= ' asac-atomic-screen';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'asac_admin_body_class' );

/** Load the small sidebar navigation enhancement only where it is needed. */
function asac_enqueue_admin_assets() {
	if ( ! asac_is_atomic_screen() ) {
		return;
	}

	wp_enqueue_style( 'asac-admin', ASAC_PLUGIN_URL . 'assets/admin.css', array(), ASAC_VERSION );
	wp_enqueue_script( 'asac-admin', ASAC_PLUGIN_URL . 'assets/admin.js', array(), ASAC_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'asac_enqueue_admin_assets' );

/** Return from the native Pattern list to the WordPress Dashboard. */
function asac_enqueue_pattern_back_script() {
	global $pagenow;
	if ( 'site-editor.php' !== $pagenow ) {
		return;
	}

	wp_enqueue_script( 'asac-pattern-back', ASAC_PLUGIN_URL . 'assets/pattern-back.js', array(), ASAC_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'asac_enqueue_pattern_back_script' );

/** Send direct visits to the parent menu to the Cards list. */
function asac_redirect_to_cards() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You cannot access atomic content.', 'as-atomic-content' ) );
	}

	$destination = post_type_exists( 'card' )
		? admin_url( 'edit.php?post_type=card' )
		: admin_url( 'site-editor.php?p=%2Fpattern&postType=wp_block' );
	wp_safe_redirect( $destination );
	exit;
}

/** Explain the required ACF installation to administrators. */
function asac_acf_required_notice() {
	if ( asac_acf_available() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>' . esc_html__( 'AS Atomic Content requires Advanced Custom Fields 6.1 or later to register Cards and Rows and migrate legacy Patterns.', 'as-atomic-content' ) . '</p></div>';
}
add_action( 'admin_notices', 'asac_acf_required_notice' );

/** Report migration progress or a recoverable failure to administrators. */
function asac_pattern_migration_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ASAC_PATTERN_MIGRATION_VERSION === get_option( 'asac_pattern_migration_version' ) ) {
		$completed = (int) get_option( 'asac_pattern_migration_success_notice', 0 );
		if ( $completed ) {
			$message = sprintf( _n( '%d legacy Pattern was copied to a native unsynced pattern.', '%d legacy Patterns were copied to native unsynced patterns.', $completed, 'as-atomic-content' ), $completed );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			delete_option( 'asac_pattern_migration_success_notice' );
		}
		return;
	}

	$state  = get_option( 'asac_pattern_migration_state', array() );
	$error  = isset( $state['error'] ) ? $state['error'] : '';
	$copied = isset( $state['copied'] ) ? (int) $state['copied'] : 0;
	if ( $error ) {
		$message = sprintf( __( 'Pattern migration paused after %1$d copies: %2$s It will retry automatically.', 'as-atomic-content' ), $copied, $error );
		if ( ! empty( $state['failed_id'] ) ) {
			$message .= ' ' . sprintf( __( 'Source post: %d.', 'as-atomic-content' ), (int) $state['failed_id'] );
		}
		echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
		return;
	}

	$message = sprintf( __( 'Migrating legacy Patterns to native unsynced patterns. %d copied so far.', 'as-atomic-content' ), $copied );
	echo '<div class="notice notice-info"><p>' . esc_html( $message ) . '</p></div>';
}
add_action( 'admin_notices', 'asac_pattern_migration_notice' );

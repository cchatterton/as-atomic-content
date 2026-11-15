<?php
/**
 * File-backed, progressively enhanced Atoms help.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discover packaged HTML guides. The filename sets order and URL slug; H1 sets the label.
 *
 * @return array<string,array{title:string,html:string}>
 */
function asac_help_guides() {
	$files = glob( ASAC_PLUGIN_DIR . 'guides/[0-9][0-9]-*.html' );
	$guides = array();
	if ( false === $files ) {
		return $guides;
	}

	sort( $files, SORT_STRING );
	foreach ( $files as $file ) {
		$name = basename( $file );
		if ( is_link( $file ) || ! is_file( $file ) || ! is_readable( $file ) || ! preg_match( '/^[0-9]{2}-([a-z0-9]+(?:-[a-z0-9]+)*)\.html$/', $name, $matches ) ) {
			continue;
		}

		$html = wp_kses_post( (string) file_get_contents( $file ) );
		if ( ! preg_match( '/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $heading ) ) {
			continue;
		}

		$title = trim( wp_specialchars_decode( wp_strip_all_tags( $heading[1] ), ENT_QUOTES ) );
		if ( '' === $title || isset( $guides[ $matches[1] ] ) ) {
			continue;
		}

		$guides[ $matches[1] ] = array(
			'title' => $title,
			'html'  => $html,
		);
	}

	return $guides;
}

/** Render the help page with an initial guide for ordinary links and no-JS use. */
function asac_render_help_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You cannot view Atoms help.', 'as-atomic-content' ) );
	}

	$guides = asac_help_guides();
	$slug   = isset( $_GET['guide'] ) ? sanitize_key( wp_unslash( $_GET['guide'] ) ) : '';
	if ( ! isset( $guides[ $slug ] ) ) {
		$slug = (string) key( $guides );
	}
	?>
	<div class="wrap asac-help" data-current-guide="<?php echo esc_attr( $slug ); ?>">
		<h1><?php esc_html_e( 'Atoms Help', 'as-atomic-content' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Practical guides to the design system, Cards, Rows, and WordPress Patterns.', 'as-atomic-content' ); ?></p>
		<div class="asac-help-layout">
			<nav class="asac-help-sidebar" aria-label="<?php esc_attr_e( 'Help topics', 'as-atomic-content' ); ?>">
				<h2><?php esc_html_e( 'Topics', 'as-atomic-content' ); ?></h2>
				<ul class="asac-help-topics">
					<?php foreach ( $guides as $guide_slug => $guide ) : ?>
						<li><a data-guide="<?php echo esc_attr( $guide_slug ); ?>" href="<?php echo esc_url( add_query_arg( 'guide', $guide_slug, admin_url( 'admin.php?page=as-atomic-content-help' ) ) ); ?>"<?php echo $guide_slug === $slug ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $guide['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<div class="asac-help-outline" aria-label="<?php esc_attr_e( 'On this page', 'as-atomic-content' ); ?>"></div>
			</nav>
			<main class="asac-help-article" id="asac-help-article" tabindex="-1" aria-live="polite">
				<?php if ( isset( $guides[ $slug ] ) ) : ?>
					<?php echo $guides[ $slug ]['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by wp_kses_post in asac_help_guides(). ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No help guides are available yet.', 'as-atomic-content' ); ?></p>
				<?php endif; ?>
			</main>
		</div>
		<p class="asac-help-status screen-reader-text" role="status"></p>
	</div>
	<?php
}

/** Serve only discovered guides to authorised admins through WordPress AJAX. */
function asac_ajax_help_guide() {
	check_ajax_referer( 'asac_help_guide', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'You cannot view Atoms help.', 'as-atomic-content' ) ), 403 );
	}

	$slug   = isset( $_POST['guide'] ) ? sanitize_key( wp_unslash( $_POST['guide'] ) ) : '';
	$guides = asac_help_guides();
	if ( ! isset( $guides[ $slug ] ) ) {
		wp_send_json_error( array( 'message' => __( 'That guide was not found.', 'as-atomic-content' ) ), 404 );
	}

	wp_send_json_success(
		array(
			'slug'  => $slug,
			'title' => $guides[ $slug ]['title'],
			'html'  => $guides[ $slug ]['html'],
		)
	);
}
add_action( 'wp_ajax_asac_help_guide', 'asac_ajax_help_guide' );

/** Load help assets on this one admin page. */
function asac_enqueue_help_assets( $hook ) {
	if ( empty( $GLOBALS['asac_help_hook'] ) || $hook !== $GLOBALS['asac_help_hook'] ) {
		return;
	}

	wp_enqueue_style( 'asac-help', ASAC_PLUGIN_URL . 'assets/help.css', array(), ASAC_VERSION );
	wp_enqueue_script( 'asac-help', ASAC_PLUGIN_URL . 'assets/help.js', array(), ASAC_VERSION, true );
	wp_add_inline_script(
		'asac-help',
		'window.ASAC_HELP = ' . wp_json_encode(
			array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'asac_help_guide' ),
			'error'   => __( 'The guide could not be loaded. Please try again.', 'as-atomic-content' ),
		)
		) . ';',
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'asac_enqueue_help_assets' );

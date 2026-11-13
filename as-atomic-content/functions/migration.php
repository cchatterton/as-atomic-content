<?php
/**
 * One-time migration from the legacy Pattern post type to native patterns.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASAC_PATTERN_MIGRATION_VERSION', '1' );
define( 'ASAC_PATTERN_MIGRATION_BATCH_SIZE', 25 );

/** Queue the first batch when the plugin is activated. */
function asac_activate_pattern_migration( $network_wide = false ) {
	if ( is_multisite() && $network_wide ) {
		foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $site_id ) {
			switch_to_blog( $site_id );
			asac_schedule_pattern_migration();
			restore_current_blog();
		}
		return;
	}

	asac_schedule_pattern_migration();
}

/** Also detect updates, which do not fire the activation hook. */
function asac_schedule_pattern_migration() {
	if ( ASAC_PATTERN_MIGRATION_VERSION === get_option( 'asac_pattern_migration_version' ) ) {
		return;
	}
	$state = get_option( 'asac_pattern_migration_state', array() );
	if ( ! empty( $state['retry_after'] ) && time() < (int) $state['retry_after'] ) {
		return;
	}

	if ( ! wp_next_scheduled( 'asac_migrate_pattern_batch' ) ) {
		wp_schedule_single_event( time(), 'asac_migrate_pattern_batch' );
	}
}
add_action( 'init', 'asac_schedule_pattern_migration', 100 );

/** Find a native copy that was created by a previous migration attempt. */
function asac_find_migrated_pattern( $legacy_id ) {
	$native_id = (int) get_post_meta( $legacy_id, '_asac_native_pattern_id', true );
	if ( $native_id && 'wp_block' === get_post_type( $native_id ) && 'trash' !== get_post_status( $native_id ) ) {
		return $native_id;
	}

	$matches = get_posts(
		array(
			'post_type'        => 'wp_block',
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_asac_legacy_pattern_id',
			'meta_value'       => $legacy_id,
			'suppress_filters' => true,
		)
	);

	return $matches ? (int) $matches[0] : 0;
}

/** Copy one legacy post, leaving the original available for recovery. */
function asac_copy_legacy_pattern( $legacy_id ) {
	$legacy = get_post( $legacy_id );
	if ( ! $legacy || 'pattern' !== $legacy->post_type ) {
		return new WP_Error( 'asac_missing_pattern', __( 'The legacy pattern could not be read.', 'as-atomic-content' ) );
	}

	$native_id = asac_find_migrated_pattern( $legacy_id );
	if ( ! $native_id ) {
		$native_id = wp_insert_post(
			array(
				'post_type'     => 'wp_block',
				'post_status'   => $legacy->post_status,
				'post_title'    => wp_slash( $legacy->post_title ),
				'post_content'  => wp_slash( $legacy->post_content ),
				'post_excerpt'  => wp_slash( $legacy->post_excerpt ),
				'post_author'   => (int) $legacy->post_author,
				'post_date'     => $legacy->post_date,
				'post_date_gmt' => $legacy->post_date_gmt,
			),
			true
		);
		if ( is_wp_error( $native_id ) ) {
			return $native_id;
		}
	}

	update_post_meta( $native_id, '_asac_legacy_pattern_id', $legacy_id );
	update_post_meta( $native_id, 'wp_pattern_sync_status', 'unsynced' );
	update_post_meta( $legacy_id, '_asac_native_pattern_id', $native_id );

	if ( 'unsynced' !== get_post_meta( $native_id, 'wp_pattern_sync_status', true )
		|| (int) get_post_meta( $native_id, '_asac_legacy_pattern_id', true ) !== $legacy_id
		|| (int) get_post_meta( $legacy_id, '_asac_native_pattern_id', true ) !== $native_id ) {
		return new WP_Error( 'asac_pattern_meta_failed', __( 'The native pattern could not be verified.', 'as-atomic-content' ) );
	}

	return $native_id;
}

/** Process a bounded batch, then queue the next one until every source is copied. */
function asac_migrate_pattern_batch() {
	if ( ASAC_PATTERN_MIGRATION_VERSION === get_option( 'asac_pattern_migration_version' ) ) {
		return;
	}
	$pending_state = get_option( 'asac_pattern_migration_state', array() );
	if ( ! empty( $pending_state['retry_after'] ) && time() < (int) $pending_state['retry_after'] ) {
		return;
	}

	$locked_at = (int) get_option( 'asac_pattern_migration_lock', 0 );
	if ( $locked_at && time() - $locked_at > 300 ) {
		delete_option( 'asac_pattern_migration_lock' );
	}
	if ( ! add_option( 'asac_pattern_migration_lock', time(), '', false ) ) {
		return;
	}

	try {
		if ( ! asac_acf_available() || ! post_type_exists( 'pattern' ) || ! post_type_exists( 'wp_block' ) ) {
			asac_record_pattern_migration_error( __( 'ACF and both pattern post types must be available to migrate patterns.', 'as-atomic-content' ) );
			return;
		}

		$state = get_option( 'asac_pattern_migration_state', array() );
		$state = wp_parse_args( $state, array( 'offset' => 0, 'copied' => 0, 'error' => '' ) );
		$ids   = get_posts(
			array(
				'post_type'        => 'pattern',
				'post_status'      => array( 'publish', 'private', 'draft', 'pending', 'future' ),
				'posts_per_page'   => ASAC_PATTERN_MIGRATION_BATCH_SIZE,
				'offset'           => (int) $state['offset'],
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);

		foreach ( $ids as $legacy_id ) {
			$already_copied = (bool) asac_find_migrated_pattern( $legacy_id );
			$result         = asac_copy_legacy_pattern( $legacy_id );
			if ( is_wp_error( $result ) ) {
				asac_record_pattern_migration_error( $result->get_error_message(), $legacy_id );
				return;
			}
			++$state['offset'];
			if ( ! $already_copied ) {
				++$state['copied'];
			}
			$state['error'] = '';
			$state['failed_id'] = 0;
			$state['retry_after'] = 0;
			update_option( 'asac_pattern_migration_state', $state, false );
		}

		if ( count( $ids ) < ASAC_PATTERN_MIGRATION_BATCH_SIZE ) {
			update_option( 'asac_pattern_migration_version', ASAC_PATTERN_MIGRATION_VERSION, false );
			if ( $state['offset'] ) {
				update_option( 'asac_pattern_migration_success_notice', (int) $state['offset'], false );
			}
			return;
		}
		asac_schedule_pattern_migration();
	} finally {
		delete_option( 'asac_pattern_migration_lock' );
	}
}
add_action( 'asac_migrate_pattern_batch', 'asac_migrate_pattern_batch' );
add_action( 'admin_init', 'asac_migrate_pattern_batch' );

/** Keep the source ID and error visible so an incomplete copy can be retried. */
function asac_record_pattern_migration_error( $message, $legacy_id = 0 ) {
	$state              = get_option( 'asac_pattern_migration_state', array() );
	$state['error']     = $message;
	$state['failed_id'] = (int) $legacy_id;
	$state['retry_after'] = time() + 300;
	update_option( 'asac_pattern_migration_state', $state, false );
}

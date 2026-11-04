<?php
/**
 * Signed-in viewing and crawler protection for Patterns.
 *
 * @package AS_Atomic_Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Keep anonymous pattern queries, including feeds, from returning posts. */
function asac_restrict_public_pattern_query( $query ) {
	if ( is_admin() || is_user_logged_in() || ! $query->is_main_query() ) {
		return;
	}

	$post_types = $query->get( 'post_type' );
	if ( 'pattern' === $post_types ) {
		$query->set( 'post_type', 'post' );
		$query->set( 'post__in', array( 0 ) );
	} elseif ( is_array( $post_types ) && in_array( 'pattern', $post_types, true ) ) {
		$allowed_post_types = array_values( array_diff( $post_types, array( 'pattern' ) ) );
		if ( $allowed_post_types ) {
			$query->set( 'post_type', $allowed_post_types );
		} else {
			$query->set( 'post_type', 'post' );
			$query->set( 'post__in', array( 0 ) );
		}
	}
}
add_action( 'pre_get_posts', 'asac_restrict_public_pattern_query' );

/** Return a normal 404 for anonymous visits to single Pattern URLs. */
function asac_restrict_pattern_page() {
	if ( ! is_singular( 'pattern' ) ) {
		return;
	}

	nocache_headers();
	if ( is_user_logged_in() ) {
		return;
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
}
add_action( 'template_redirect', 'asac_restrict_pattern_page', 0 );

/** Mark permitted Pattern pages as ineligible for indexing. */
function asac_pattern_robots( $robots ) {
	if ( is_singular( 'pattern' ) ) {
		$robots['noindex'] = true;
		$robots['nofollow'] = true;
		unset( $robots['index'], $robots['follow'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'asac_pattern_robots' );

/** Send a robots header even if the active theme omits wp_head(). */
function asac_pattern_headers( $headers ) {
	if ( is_singular( 'pattern' ) ) {
		$headers['X-Robots-Tag'] = 'noindex, nofollow';
	}
	return $headers;
}
add_filter( 'wp_headers', 'asac_pattern_headers' );

/** Hide Patterns from the core sitemap even if another setting changes visibility. */
function asac_exclude_pattern_from_sitemap( $post_types ) {
	unset( $post_types['pattern'] );
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'asac_exclude_pattern_from_sitemap' );

/** Deny anonymous access to the Pattern REST collection and single records. */
function asac_restrict_pattern_rest( $result, $server, $request ) {
	if ( null !== $result || is_user_logged_in() ) {
		return $result;
	}

	$post_type = get_post_type_object( 'pattern' );
	if ( ! $post_type || ! $post_type->show_in_rest ) {
		return $result;
	}

	$namespace = $post_type->rest_namespace ? $post_type->rest_namespace : 'wp/v2';
	$base      = $post_type->rest_base ? $post_type->rest_base : 'pattern';
	$route     = $request->get_route();
	if ( $route === '/' . $namespace . '/' . $base || 0 === strpos( $route, '/' . $namespace . '/' . $base . '/' ) ) {
		return new WP_Error( 'rest_no_route', __( 'No route was found matching the URL and request method.', 'as-atomic-content' ), array( 'status' => 404 ) );
	}
	return $result;
}
add_filter( 'rest_pre_dispatch', 'asac_restrict_pattern_rest', 10, 3 );

/** Remove Patterns from anonymous REST search, including subtype=any. */
function asac_restrict_pattern_rest_search( $query_args, $request ) {
	if ( is_user_logged_in() || ! isset( $query_args['post_type'] ) ) {
		return $query_args;
	}

	$post_types = (array) $query_args['post_type'];
	if ( ! in_array( 'pattern', $post_types, true ) ) {
		return $query_args;
	}

	$post_types = array_values( array_diff( $post_types, array( 'pattern' ) ) );
	if ( $post_types ) {
		$query_args['post_type'] = $post_types;
	} else {
		$query_args['post_type'] = 'post';
		$query_args['post__in']  = array( 0 );
	}
	return $query_args;
}
add_filter( 'rest_post_search_query', 'asac_restrict_pattern_rest_search', 10, 2 );

/** Suppress anonymous oEmbed responses for Patterns. */
function asac_restrict_pattern_oembed( $data, $post ) {
	if ( ! is_user_logged_in() && 'pattern' === $post->post_type ) {
		return false;
	}
	return $data;
}
add_filter( 'oembed_response_data', 'asac_restrict_pattern_oembed', 10, 2 );

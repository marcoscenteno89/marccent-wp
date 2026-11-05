<?php
/**
 * Marccent WP: the page frame and the theme's options (inc/theme-options.php). The look, the theme engine and the
 * blocks come from the Marccent Blocks plugin.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'custom-logo' );
	add_theme_support( 'menus' );
} );

require __DIR__ . '/inc/theme-options.php';
require __DIR__ . '/inc/performance.php'; // what WordPress adds to every page that a Marccent site doesn't use // the site's theme (design, colors, mode, glass, hover…) edited in the Site Editor

// The patterns (patterns/*.php, written by tools/patterns.js) under their own heading in the inserter.
add_action( 'init', function () {
	register_block_pattern_category( 'marccent', array( 'label' => __( 'Marccent', 'marccent-wp' ), 'description' => __( 'The sections a Marccent site starts from.', 'marccent-wp' ) ) );
} );

// Without the plugin the templates' blocks can't render: say so to whoever can fix it.
add_action( 'admin_notices', function () {
	if ( ! function_exists( 'marccent_blocks_defaults' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>Marccent WP needs the <strong>Marccent Blocks</strong> plugin: activate it under Plugins.</p></div>';
	}
} );

/*
 * Tools → Marccent export (Marccent Blocks): the header, footer and other template parts as edited on the site (the
 * database's copies; the theme's own parts/*.html need no export).
 */
add_filter( 'marccent_export_parts', function ( $parts ) {
	$theme          = get_stylesheet();
	$parts['parts'] = array(
		'label'  => __( 'Header, footer and other template parts', 'marccent-wp' ),
		'export' => function () use ( $theme ) {
			$out = array();
			foreach ( get_block_templates( array(), 'wp_template_part' ) as $part ) {
				if ( 'custom' === $part->source && $theme === $part->theme ) {
					$out[ $part->slug ] = array( 'title' => $part->title, 'area' => $part->area, 'content' => $part->content );
				}
			}
			return $out;
		},
		'import' => function ( array $data ) use ( $theme ) {
			if ( ! current_user_can( 'edit_theme_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				return new WP_Error( 'marccent_wp', __( 'Only someone who can edit the site’s theme can import its template parts.', 'marccent-wp' ) );
			}
			foreach ( $data as $slug => $p ) {
				$slug     = sanitize_title( (string) $slug );
				$existing = get_posts( array( 'post_type' => 'wp_template_part', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'tax_query' => array( array( 'taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => $theme ) ) ) );
				$id       = wp_insert_post( array(
					'ID'           => $existing ? $existing[0]->ID : 0,
					'post_type'    => 'wp_template_part',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => sanitize_text_field( (string) ( $p['title'] ?? $slug ) ),
					'post_content' => wp_slash( (string) ( $p['content'] ?? '' ) ),
				), true );
				if ( is_wp_error( $id ) ) {
					return $id;
				}
				wp_set_object_terms( $id, $theme, 'wp_theme' );
				wp_set_object_terms( $id, sanitize_key( (string) ( $p['area'] ?? 'uncategorized' ) ), 'wp_template_part_area' );
			}
		},
	);
	return $parts;
} );

/*
 * Appearance → Editor (the site editor) is off: the site's frame is this theme's templates and parts, with Marccent's
 * blocks, and the site editor can't edit them. Its menu item and the toolbar's Edit Site go, and the screen sends
 * anyone who opens it to Themes; its patterns screens (?p=/pattern…) stay reachable, as Appearance → Patterns (the
 * Marccent ones first).
 */
add_action( 'admin_menu', function () {
	remove_submenu_page( 'themes.php', 'site-editor.php' );
	add_submenu_page( 'themes.php', __( 'Patterns', 'marccent-wp' ), __( 'Patterns', 'marccent-wp' ), 'edit_theme_options', 'site-editor.php?p=%2Fpattern&categoryId=marccent' );
}, 99 );
add_action( 'admin_bar_menu', fn( $bar ) => $bar->remove_node( 'site-editor' ), 99 );
add_action( 'load-site-editor.php', function () {
	if ( ! str_starts_with( (string) ( $_GET['p'] ?? '' ), '/pattern' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- only read to route
		wp_safe_redirect( admin_url( 'themes.php' ) );
		exit;
	}
} );

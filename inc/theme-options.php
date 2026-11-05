<?php
/**
 * The theme's options: the site's starting theme (the marccent_defaults option, which Marccent Blocks paints on every
 * page) on a page of its own, Appearance → Marccent theme (assets/theme-options.js), not in the editors: a page's
 * editor changes that page, never the whole site's look. Everything marccent-theme lets a site set: the design style,
 * the palette and the site's own palettes, light or dark, glass, the default hover, the dark and light colors, CSS
 * variables and how long a visitor's own choices last. A preview of the site shows each change as it's made; Save
 * saves it.
 *
 * What there is to choose from comes from Marccent Blocks' copy of the package (marccent_blocks_theme_options: the
 * design styles and hovers; marccent_blocks_theme_base: its defaults). The option goes through REST as the site's
 * setting marccent_defaults (POST /wp/v2/settings), checked here: only what the theme knows, each value of its kind.
 */

defined( 'ABSPATH' ) || exit;

// A #rgb or #rrggbb color, or null.
function marccent_wp_hex( $value ): ?string {
	return is_string( $value ) && preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value ) ? strtolower( $value ) : null;
}

// The theme as saved: what the theme knows, each value checked; anything else dropped.
function marccent_wp_theme_sanitize( $value ): string {
	$in      = is_array( $value ) ? $value : json_decode( (string) $value, true );
	$in      = is_array( $in ) ? $in : array();
	$options = function_exists( 'marccent_blocks_theme_options' ) ? marccent_blocks_theme_options() : array( 'designs' => array(), 'hovers' => array() );
	$out     = array();

	// Without Marccent Blocks there's no list to check against: a plain name is kept rather than lost.
	$known = fn( $value, array $list ) => is_string( $value ) && ( $list ? in_array( $value, $list, true ) : sanitize_key( $value ) === $value );
	if ( $known( $in['design'] ?? null, array_column( $options['designs'], 'id' ) ) ) {
		$out['design'] = $in['design'];
	}
	foreach ( array( 'isDark', 'glass' ) as $key ) {
		if ( isset( $in[ $key ] ) ) {
			$out[ $key ] = (bool) $in[ $key ];
		}
	}
	if ( isset( $in['hover'] ) && ( 'none' === $in['hover'] || $known( $in['hover'], $options['hovers'] ) ) ) {
		$out['hover'] = $in['hover'];
	}
	foreach ( array( 'dark', 'light' ) as $key ) {
		if ( marccent_wp_hex( $in[ $key ] ?? null ) ) {
			$out[ $key ] = marccent_wp_hex( $in[ $key ] );
		}
	}
	// What visitors may change themselves: the full mini theme editor, or single controls of it.
	$visitor = array();
	foreach ( array( 'full', 'design', 'mode', 'glass', 'palette' ) as $key ) {
		if ( isset( $in['visitor'][ $key ] ) ) {
			$visitor[ $key ] = (bool) $in['visitor'][ $key ];
		}
	}
	if ( $visitor ) {
		$out['visitor'] = $visitor;
	}
	if ( isset( $in['expiresDays'] ) ) {
		$out['expiresDays'] = max( 0, min( 365, (int) $in['expiresDays'] ) );
	}
	// The site's own palettes: an id and two colors each.
	$palettes = array();
	foreach ( (array) ( $in['palettes'] ?? array() ) as $p ) {
		$id = sanitize_key( $p['id'] ?? '' );
		if ( $id && marccent_wp_hex( $p['primary'] ?? null ) && marccent_wp_hex( $p['secondary'] ?? null ) && count( $palettes ) < 20 ) {
			$palettes[ $id ] = array( 'id' => $id, 'primary' => marccent_wp_hex( $p['primary'] ), 'secondary' => marccent_wp_hex( $p['secondary'] ) );
		}
	}
	if ( $palettes ) {
		$out['palettes'] = array_values( $palettes );
	}
	if ( isset( $in['palette'] ) && sanitize_key( $in['palette'] ) === $in['palette'] && '' !== $in['palette'] ) {
		$out['palette'] = $in['palette'];
	}
	// CSS variables: --name: value, a value with nothing that could close the rule.
	$vars = array();
	foreach ( (array) ( $in['vars'] ?? array() ) as $name => $v ) {
		if ( preg_match( '/^--[a-z][a-z0-9-]{0,63}$/', (string) $name ) && is_scalar( $v ) && '' !== trim( (string) $v ) && ! preg_match( '/[;{}<>]/', (string) $v ) ) {
			$vars[ $name ] = trim( (string) $v );
		}
	}
	if ( $vars ) {
		$out['vars'] = $vars;
	}
	return (string) wp_json_encode( (object) $out );
}

add_action( 'init', function () {
	register_setting( 'marccent', 'marccent_defaults', array(
		'type'              => 'string',
		'description'       => __( 'The site theme (Marccent theme panel)', 'marccent-wp' ),
		'default'           => '{}',
		'sanitize_callback' => 'marccent_wp_theme_sanitize',
		'show_in_rest'      => true,
	) );
} );

// Appearance → Marccent theme: a page of its own, so the site's look is changed on purpose, not from any editor.
add_action( 'admin_menu', function () {
	$hook = add_theme_page( __( 'Marccent theme', 'marccent-wp' ), __( 'Marccent theme', 'marccent-wp' ), 'manage_options', 'marccent-theme', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Marccent theme', 'marccent-wp' ) . '</h1>';
		if ( ! function_exists( 'marccent_blocks_theme_options' ) ) {
			echo '<p>' . esc_html__( 'The theme’s choices come from the Marccent Blocks plugin: activate it under Plugins.', 'marccent-wp' ) . '</p></div>';
			return;
		}
		echo '<p>' . esc_html__( 'The whole site’s look. Changes show in the preview; the site changes when you press Save.', 'marccent-wp' ) . '</p>';
		echo '<div id="marccent-theme-options"></div></div>';
	} );

	add_action( "admin_enqueue_scripts", function ( $current ) use ( $hook ) {
		if ( $current !== $hook || ! function_exists( 'marccent_blocks_theme_options' ) ) {
			return;
		}
		$file = get_template_directory() . '/assets/theme-options.js';
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_script( 'marccent-wp-theme-options', get_template_directory_uri() . '/assets/theme-options.js', array( 'wp-api-fetch', 'wp-components', 'wp-element' ), filemtime( $file ), true );
		wp_add_inline_script( 'marccent-wp-theme-options', 'window.marccentThemeOptions = ' . wp_json_encode( array(
			'base'    => marccent_blocks_theme_base(),
			'options' => marccent_blocks_theme_options(),
			'saved'   => (string) get_option( 'marccent_defaults', '{}' ),
			'preview' => add_query_arg( 'marccent_preview', '1', home_url( '/' ) ),
		) ) . ';', 'before' );
		// The options beside a preview of the site, which stays in view as the options scroll.
		wp_add_inline_style( 'wp-components', '.marccent-theme-page{display:grid;grid-template-columns:minmax(18rem,24rem) 1fr;gap:1.5rem;align-items:start;margin-top:1rem}'
			. '.marccent-theme-page__options{background:#fff;border:1px solid #dcdcde}'
			. '.marccent-theme-page__bar{display:flex;gap:.5rem;align-items:center;padding:12px 16px;border-bottom:1px solid #dcdcde;position:sticky;top:32px;background:#fff;z-index:2}'
			. '.marccent-theme-page__dirty{color:#996800;font-size:12px}'
			. '.marccent-theme-page__preview{position:sticky;top:48px}'
			. '.marccent-theme-page__preview iframe{width:100%;height:calc(100vh - 140px);border:1px solid #dcdcde;background:#fff}'
			. '@media (max-width:960px){.marccent-theme-page{grid-template-columns:1fr}.marccent-theme-page__preview{position:static}.marccent-theme-page__preview iframe{height:70vh}}' );
	} );
} );

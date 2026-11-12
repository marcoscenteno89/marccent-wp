<?php
/**
 * What WordPress adds to every page that a Marccent site doesn't use: emoji scripts, oEmbed discovery, feed links (no
 * blog), the generator and editing-client links, and the classic-theme styles. Images are WordPress's own: it marks the
 * first large one fetchpriority="high" and lazy-loads the rest. (From Marccent Local, which kept only what's about
 * the business.)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	add_filter( 'emoji_svg_url', '__return_false' );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

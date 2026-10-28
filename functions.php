<?php
/**
 * Marccent WP: the page frame. The look, the theme engine and the blocks come from the Marccent Blocks plugin.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'custom-logo' );
	add_theme_support( 'menus' );
} );

// Without the plugin the templates' blocks can't render: say so to whoever can fix it.
add_action( 'admin_notices', function () {
	if ( ! function_exists( 'marccent_blocks_defaults' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>Marccent WP needs the <strong>Marccent Blocks</strong> plugin: activate it under Plugins.</p></div>';
	}
} );

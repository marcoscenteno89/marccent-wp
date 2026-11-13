<?php
/**
 * Blocks the product line retired, still saved in a site's database (marccent-theme#104): a header edited in the Site
 * Editor before the Navbar block became the Header pattern keeps a marccent/navbar block, which no plugin registers
 * any more, so the page would show nothing there. On the page it's the Header pattern instead; the saved template
 * part isn't touched (Appearance → Editor → the header's "Reset" puts the theme's back).
 */

defined( 'ABSPATH' ) || exit;

const MARCCENT_WP_RETIRED = array( 'marccent/navbar' => 'marccent-wp/header' );

add_filter(
	'pre_render_block',
	function ( $pre, $block ) {
		$pattern = MARCCENT_WP_RETIRED[ $block['blockName'] ?? '' ] ?? null;
		if ( null === $pattern || WP_Block_Type_Registry::get_instance()->is_registered( $block['blockName'] ) ) {
			return $pre;
		}
		return do_blocks( '<!-- wp:pattern {"slug":"' . $pattern . '"} /-->' );
	},
	10,
	2
);

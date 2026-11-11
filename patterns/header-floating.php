<?php
/**
 * Title: Header: floating over the hero
 * Slug: marccent-wp/header-floating
 * Categories: header, marccent
 * Keywords: header, menu, navigation, floating
 * Description: The header as a pill floating over the top of the page: see-through while the page is at its top, solid once it scrolls; the first section (a hero) starts under it. The same parts as the Header.
 *
 * Written by tools/patterns.js; change it there.
 */
?>
<!-- wp:marccent/section {"as":"header","verticalPadding":"pad-y-sm","className":"fixed-top pe-none","version":1} -->
<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","className":"cols-between pill pad-x pad-y-sm bg border shadow solid-on-scroll pe-auto","version":2} -->
<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2} -->
<!-- wp:site-logo {"width":40} /-->

<!-- wp:site-title {"level":0,"className":"fw-bold"} /-->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2} -->
<!-- wp:marccent/menu {"location":"primary","layout":"auto-cols","fold":true,"className":"menu-quiet","version":2} /-->

<!-- wp:marccent/mini-theme-editor {"visitors":true,"version":1} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/section -->

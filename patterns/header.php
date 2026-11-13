<?php
/**
 * Title: Header: logo, main menu and theme controls
 * Slug: marccent-wp/header
 * Categories: header, marccent
 * Keywords: header, menu, navigation
 * Description: The site’s header, sticky at the top: the logo and name, the menu in the Primary location (Appearance → Menus) folding behind a menu button on phones, and what Appearance → Marccent theme, Visitors allows. Add a button for a call to action.
 *
 * Written by tools/patterns.js; change it there.
 */
?>
<!-- wp:marccent/section {"as":"header","verticalPadding":"pad-y-sm","surface":"bg","version":2,"className":"border-bottom sticky-top"} -->
<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2,"className":"cols-between"} -->
<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2} -->
<!-- wp:site-logo {"width":40} /-->

<!-- wp:site-title {"level":0,"className":"fw-bold"} /-->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2} -->
<!-- wp:marccent/menu {"layout":"auto-cols","fold":true,"location":"primary","version":2,"className":"menu-quiet"} /-->

<!-- wp:marccent/mini-theme-editor {"visitors":true,"version":1} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/section -->

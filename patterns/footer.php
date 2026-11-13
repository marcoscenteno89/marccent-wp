<?php
/**
 * Title: Footer: brand, link columns, legal row and social icons
 * Slug: marccent-wp/footer
 * Categories: footer, marccent
 * Keywords: footer, menu, links, social, legal
 * Description: The site’s footer: the logo, name and a line about the site beside columns of links (a menu each, Appearance → Menus), then the copyright with the legal links and the social icons. Every part is optional: delete what the site doesn’t have.
 *
 * Written by tools/patterns.js from marccent-patterns' wp/patterns.cjs; change it there.
 */
?>
<!-- wp:marccent/section {"as":"footer","verticalPadding":"pad-y-lg","gap":"gap-lg","surface":"bg","version":2,"anchor":"footer","className":"border-top"} -->
<!-- wp:marccent/grid {"columns":2,"gap":"gap-lg","version":2,"className":"layout-1-2"} -->
<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:site-logo {"width":48} /-->

<!-- wp:site-title {"level":0,"className":"fw-bold"} /-->

<!-- wp:site-tagline {"className":"opacity-75"} /-->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"columns":2,"minimumColumnWidth":"10rem","gap":"gap-lg","version":2} -->
<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:heading {"className":"h6"} -->
<h2 class="wp-block-heading h6">Services</h2>
<!-- /wp:heading -->

<!-- wp:marccent/menu {"label":"Services","menu":"footer-services","version":2,"className":"cols-1 gap-0"} /-->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:heading {"className":"h6"} -->
<h2 class="wp-block-heading h6">Company</h2>
<!-- /wp:heading -->

<!-- wp:marccent/menu {"label":"Company","menu":"footer-company","version":2,"className":"cols-1 gap-0"} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"layout":"auto-cols","gap":"gap-sm","alignItems":"items-center","version":2,"className":"layout-ends border-top pad-y"} -->
<!-- wp:paragraph {"className":"fs-sm opacity-75"} -->
<p class="fs-sm opacity-75"><small>© 2026 The business name</small></p>
<!-- /wp:paragraph -->

<!-- wp:marccent/menu {"label":"Footer legal","layout":"auto-cols","menu":"footer-legal","version":2,"className":"fs-sm"} /-->

<!-- wp:marccent/menu {"label":"Social","iconOnly":true,"layout":"auto-cols","menu":"social","version":2,"className":"col-last"} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/section -->

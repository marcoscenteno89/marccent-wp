<?php
/**
 * Title: Services: a card for each service page
 * Slug: marccent-wp/services
 * Categories: marccent, text
 * Keywords: services, cards, loop
 * Description: The pages under Services, a card each (their featured image, title and excerpt), from a Loop: a new service page shows up by itself.
 *
 * Written by tools/patterns.js; change it there.
 */
?>
<!-- wp:marccent/section {"padY":"pad-y-lg"} -->
<!-- wp:marccent/grid {"cols":1,"gap":"gap-sm","alignItems":"items-start"} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Our services</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A line on how the services fit together.</p>
<!-- /wp:paragraph -->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/loop {"postType":"page","perPage":12,"orderBy":"menu_order","order":"asc","parent":"services"} -->
<!-- wp:marccent/card {"hover":""} -->
<!-- wp:marccent/field {"key":"image"} /-->

<!-- wp:marccent/field {"tag":"h3","link":true} /-->

<!-- wp:marccent/field {"key":"excerpt","format":"words:24"} /-->

<!-- wp:marccent/button {"label":"More about {{ title }}","href":"{{ link }}"} /-->
<!-- /wp:marccent/card -->
<!-- /wp:marccent/loop -->
<!-- /wp:marccent/section -->

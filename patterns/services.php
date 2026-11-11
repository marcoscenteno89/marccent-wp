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
<!-- wp:marccent/section {"verticalPadding":"pad-y-lg","version":1} -->
<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Our services</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A line on how the services fit together.</p>
<!-- /wp:paragraph -->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/loop {"postType":"page","perPage":12,"orderBy":"menu_order","order":"asc","parent":"services"} -->
<!-- wp:marccent/card {"rows":0,"version":1} -->
<!-- wp:marccent/field {"key":"image"} /-->

<!-- wp:marccent/field {"tag":"h3","link":true} /-->

<!-- wp:marccent/field {"key":"excerpt","format":"words:24"} /-->

<!-- wp:marccent/button {"href":"{{ link }}","label":"More about {{ title }}","version":1} /-->
<!-- /wp:marccent/card -->
<!-- /wp:marccent/loop -->
<!-- /wp:marccent/section -->

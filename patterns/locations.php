<?php
/**
 * Title: Locations
 * Slug: marccent-wp/locations
 * Categories: marccent, contact
 * Keywords: locations, offices, map
 * Description: The business’s offices or shops (Site content → Locations), each with its address, phone, hours and directions.
 *
 * Written by tools/patterns.js; change it there.
 */
?>
<!-- wp:marccent/section {"verticalPadding":"pad-y-lg","version":2} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Where to find us</h2>
<!-- /wp:heading -->

<!-- wp:marccent/loop {"source":"locations","perPage":20,"from":"options"} -->
<!-- wp:marccent/card {"rows":0,"version":1} -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">{{name}}</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{{street}}, {{city}}, {{state}} {{postal_code}}</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="tel:{{telephone}}">{{phone}}</a></p>
<!-- /wp:paragraph -->

<!-- wp:marccent/field {"key":"hours"} /-->

<!-- wp:marccent/grid {"layout":"row-wrap","gap":"gap-sm","version":2} -->
<!-- wp:marccent/button {"icon":"diamond-turn-right","href":"{{map_link}}","label":"Directions","color":"secondary","version":1} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/card -->
<!-- /wp:marccent/loop -->
<!-- /wp:marccent/section -->

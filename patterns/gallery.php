<?php
/**
 * Title: Gallery: a field group’s photos
 * Slug: marccent-wp/gallery
 * Categories: gallery, marccent
 * Keywords: gallery, photos, loop
 * Description: The gallery page’s Photos rows (a repeating field group: image, caption), in a grid, with a filter visitors can use. Needs the Photos group on the gallery page.
 *
 * Written by tools/patterns.js from marccent-patterns' wp/patterns.cjs; change it there.
 */
?>
<!-- wp:marccent/section {"verticalPadding":"pad-y-lg","version":2} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Our work</h2>
<!-- /wp:heading -->

<!-- wp:marccent/loop {"source":"photos","perPage":24,"from":"gallery","gap":"gap-sm","paginate":true} -->
<!-- wp:marccent/field {"key":"image"} /-->

<!-- wp:marccent/field {"key":"caption"} /-->
<!-- /wp:marccent/loop -->
<!-- /wp:marccent/section -->

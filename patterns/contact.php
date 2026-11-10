<?php
/**
 * Title: Contact: a form, the hours and the phone
 * Slug: marccent-wp/contact
 * Categories: marccent, contact
 * Keywords: contact, form, hours
 * Description: A quote form beside the business’s hours (Site content → Business) and whether it’s open now.
 *
 * Written by tools/patterns.js; change it there.
 */
?>
<!-- wp:marccent/section {"verticalPadding":"pad-y-lg","anchor":"form","version":1} -->
<!-- wp:marccent/grid {"columns":2,"gap":"gap-lg","alignItems":"items-start","version":2} -->
<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Get a free quote</h2>
<!-- /wp:heading -->

<!-- wp:marccent/form {"successMessage":"Thanks! We got it and will be in touch within a day."} -->
<!-- wp:marccent/form-field {"name":"name","label":"Name","required":true} /-->

<!-- wp:marccent/form-field {"name":"phone","label":"Phone","type":"tel","required":true} /-->

<!-- wp:marccent/form-field {"name":"email","label":"Email","type":"email"} /-->

<!-- wp:marccent/form-field {"name":"message","label":"What do you need?","type":"textarea"} /-->

<!-- wp:marccent/form-field {"name":"photo","label":"A photo (optional)","type":"file","accept":"image/*"} /-->
<!-- /wp:marccent/form -->
<!-- /wp:marccent/grid -->

<!-- wp:marccent/grid {"columns":1,"gap":"gap-sm","alignItems":"items-start","version":2} -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Hours</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>{{ business.hours:open_now }}</strong></p>
<!-- /wp:paragraph -->

<!-- wp:marccent/field {"key":"business.hours"} /-->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/grid -->
<!-- /wp:marccent/section -->

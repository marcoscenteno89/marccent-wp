# To do: marccent-wp

The rules for this list are in [PRODUCT-LINE.md §8](https://github.com/marcoscenteno89/marccent-theme/blob/master/PRODUCT-LINE.md#8-work-lists-ticketsmd-and-each-projects-todomd):
work fixed here only; anything the theme fixes is a ticket in marccent-theme's
[TICKETS.md](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md), linked under Waiting on.

## Bugs

- [ ] **Two hero images with `fetchpriority="high"`** — `inc/performance.php`'s (moved here from Marccent Local) first-image rule can mark one image while WordPress marks another. Drop ours for core's, or skip it when core has marked one. (found 2026-11-05)

## Requests

## Waiting on

- [ ] **[T-002](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — the cookie banner is printed from PHP (`inc/tracking.php`, `wp_footer`; RULES.md A6) with inline CSS and scripts: once the theme's consent component ships, a block in the footer template part replaces it (found 2026-11-05)
- [ ] **[T-003](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — the Call bar pattern (Call and Get a quote buttons fixed to the bottom on phones), replacing Marccent Local's `inc/callbar.php` (found 2026-11-05)
- [ ] **[T-022](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — the theme options page is hand-built (`inc/theme-options.php` inline CSS, `assets/theme-options.js`) (found 2026-11-08)

## After 1.0

## Notes

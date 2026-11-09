# Marccent WP

A WordPress block theme on the Marccent design system ([marccent-theme](https://github.com/marcoscenteno89/marccent-theme)): the same look, theme engine and components as the Marccent Next.js site.

It is the page frame only (templates, header and footer parts, `theme.json`). The styles, the runtime and the blocks come from the [Marccent Blocks](https://github.com/marcoscenteno89/marccent-blocks) plugin, which it requires: one shared copy of the theme at one version (pinned there to a marccent-theme release), whatever other Marccent plugins the site runs.

## Theme options

Appearance → Marccent theme: the site's design style, palette (and its own palettes), light or dark, glass, default hover, dark and light colors, CSS variables and how long a visitor's own theme choice lasts, beside a preview of the site that changes as you pick. Nothing changes on the site until Save (the `marccent_defaults` option, which Marccent Blocks paints on every page); a page's editor never changes the whole site's look. Colors that fail WCAG AA contrast get a warning. The choices come from the pinned marccent-theme release, so a new design style or hover there shows up here.

Related plugins: [Marccent Blocks](https://github.com/marcoscenteno89/marccent-blocks) (the theme's styles, runtime and blocks) and [Marccent Content](https://github.com/marcoscenteno89/marccent-content) (custom post types, fields and loops).

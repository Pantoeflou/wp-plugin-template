# Templates

Templates are PHP view files that render HTML.

This template supports **theme overrides** via `WP_Forever_Template_Loader`.

## Override path

Themes can override plugin templates by placing a matching file in:

`{theme}/wp-forever/{relative-template-path}`

Example:

- Plugin template: `templates/shortcodes/example.php`
- Theme override: `{theme}/wp-forever/shortcodes/example.php`

## Guidelines

- Keep templates focused on output (minimal logic).
- Pass variables in via `WP_Forever_Template_Loader::render()`.
- Escape output (`esc_html`, `esc_attr`, `wp_kses_post`) appropriately.
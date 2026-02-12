# WP Forever - WordPress Plugin Template

A well-documented, opinionated WordPress plugin template that eliminates repetitive boilerplate while teaching best practices.

## Introduction

WP Forever is a **plugin template**, not a framework. It provides a clean starting point with:

- **Clear structure** — Opinionated directory layout that's easy to navigate
- **Comment-heavy code** — Explains the "why", not just the "what"
- **Security baseline** — Nonces, capability checks, sanitization baked in
- **WordPress-native patterns** — Uses core APIs, not custom abstractions

Copy this template, rename it, and start building your plugin immediately.

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.0+ |
| WordPress | 6.0+ |
| MySQL | 5.7+ or MariaDB 10.3+ |

**No external dependencies required.** Works on standard shared hosting.

## Quick Start

1. **Copy the template**
   ```bash
   cp -r wp-forever my-plugin
   cd my-plugin
   ```

2. **Rename everything** (see [CHECKLIST.md](CHECKLIST.md) for detailed steps)
   - Rename `wp-forever.php` → `my-plugin.php`
   - Find/replace `wp-forever` → `my-plugin` (text domain)
   - Find/replace `WP_FOREVER_` → `MY_PLUGIN_` (constants)
   - Find/replace `wp_forever_` → `my_plugin_` (functions)
   - Find/replace `WP_Forever_` → `My_Plugin_` (classes)

3. **Update plugin header** in `my-plugin.php`
   ```php
   Plugin Name: My Plugin
   Description: What your plugin does.
   Author: Your Name
   ```

4. **Test activation**
   - Copy to `wp-content/plugins/`
   - Activate via WordPress admin
   - Check for errors

5. **Start building!**

## Directory Structure

```
wp-forever/
├── wp-forever.php      # Main plugin file (entry point)
├── uninstall.php       # Cleanup on plugin deletion
├── core/               # Plugin orchestration classes
├── admin/              # Admin-specific code (settings page + notices)
├── public/             # Front-end code (shortcodes + public AJAX)
├── includes/           # Shared utilities (DB, REST, template loader, helpers)
├── integrations/       # Optional integrations (e.g. WooCommerce)
├── templates/          # Theme-overridable templates
├── assets/             # CSS, JS, images
├── languages/          # Translation files
└── docs/               # Documentation
```

See [ARCHITECTURE.md](ARCHITECTURE.md) for detailed explanation of each directory.

## Customization

### Adding Admin Pages

Admin menus and settings pages go in `admin/class-admin.php`:

```php
// In WP_Forever_Admin class:
public function add_menu_pages(): void {
    add_menu_page(
        __( 'My Plugin', 'wp-forever' ),
        __( 'My Plugin', 'wp-forever' ),
        'manage_options',
        'my-plugin',
        array( $this, 'render_admin_page' )
    );
}
```

### Adding Shortcodes

Shortcodes go in `public/class-public.php`.

This template already includes an example shortcode:
`[wp_forever_example]` → renders `templates/shortcodes/example.php` (theme-overridable).

```php
// In WP_Forever_Public class:
public function register_shortcodes(): void {
    add_shortcode( 'my_shortcode', array( $this, 'render_shortcode' ) );
}
```

### Adding AJAX Handlers

AJAX handlers go in the appropriate class (admin or public).

This template ships a small helper `WP_Forever_AJAX` that standardizes:
- POST enforcement
- nonce verification
- capability checks
- JSON success/error responses

```php
// Register the handler:
add_action( 'wp_ajax_my_action', array( $this, 'handle_ajax' ) );

// The handler method:
public function handle_ajax(): void {
    WP_Forever_AJAX::require_post();
    WP_Forever_AJAX::verify_nonce( 'wp_forever_public' );

    WP_Forever_AJAX::require_cap( 'manage_options' );

    // Process request...

    WP_Forever_AJAX::send_success( array( 'message' => 'Done' ) );
}
```

### Adding REST Endpoints

REST routes are defined in `includes/rest/class-rest.php`.

The template includes an example endpoint:
- `GET /wp-json/wp-forever/v1/ping`

You can toggle registration via **Settings → WP Forever → Advanced → “Enable REST API”**.

### Database (Custom Tables)

If you need custom tables:
- Define schema in `includes/db/schema.php` (bump `WP_FOREVER_DB_SCHEMA_VERSION` when it changes)
- Install/upgrade uses `dbDelta()` via `WP_Forever_Services::db()->install_or_upgrade()`

By default the template includes a small example events table (`wp_forever_events`).

## Security

This template includes security patterns throughout. Key principles:

1. **Always verify nonces** for form submissions and AJAX
2. **Always check capabilities** before privileged actions
3. **Always sanitize input** before processing
4. **Always escape output** before displaying

See code comments for specific examples of each pattern.

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes with clear commit messages
4. Submit a pull request

## License

GPL-2.0-or-later — See [LICENSE](../LICENSE) for details.

This license is compatible with WordPress.org plugin directory requirements.

---

**Need help?** Check the detailed [ARCHITECTURE.md](ARCHITECTURE.md) or [CHECKLIST.md](CHECKLIST.md).

# WP Forever - Architecture

This document explains the structural decisions and conventions used in this plugin template.

## Philosophy

### WordPress-Native

We use WordPress APIs and patterns, not custom abstractions:

- **Hooks & Filters** — Primary extension mechanism
- **Settings API** — For admin options pages
- **Options API** — For storing settings
- **Transients API** — For caching
- **WP-Cron** — For scheduled tasks

### Readable Over Clever

Code should be understandable at a glance:

- Explicit `require_once` statements (no magic autoloaders)
- Linear top-to-bottom file flow
- Comments explain "why", not just "what"

### Modular But Simple

Components are separate but not over-engineered:

- No dependency injection containers
- No abstract factory patterns
- Thin classes that do one thing well

### No Hard Dependencies

Works on any WordPress host:

- No Composer requirement (manual requires)
- No build tools (vanilla CSS/JS)
- No exotic PHP extensions

---

## Directory Layout

```
wp-forever/
├── wp-forever.php          # Entry point
├── uninstall.php           # Cleanup on deletion
├── core/                   # Bootstrap & lifecycle
├── admin/                  # Admin-only code
├── public/                 # Front-end code
├── includes/               # Shared utilities (DB, REST, AJAX helpers, template loader)
├── integrations/           # Optional integrations (e.g. WooCommerce)
├── templates/              # Theme-overridable templates
├── assets/                 # Static assets
├── languages/              # i18n files
└── docs/                   # Documentation
```

### `wp-forever.php` — Entry Point

The main plugin file that WordPress loads. Responsibilities:

1. Define plugin header (name, version, requirements)
2. Prevent direct access (ABSPATH check)
3. Define constants
4. Load required files
5. Register activation/deactivation hooks
6. Initialize plugin on `plugins_loaded`

### `uninstall.php` — Cleanup

Runs when plugin is **deleted** (not deactivated). Cleans up:

- Options from `wp_options` table
- Transients
- User meta
- Custom database tables
- Uploaded files

### `core/` — Bootstrap & Lifecycle

Plugin orchestration and lifecycle management:

| File | Purpose |
|------|---------|
| `class-plugin.php` | Main orchestrator — registers hooks |
| `class-activator.php` | Runs on plugin activation |
| `class-deactivator.php` | Runs on plugin deactivation |
| `class-i18n.php` | Loads translation files |

### `admin/` — Admin-Only Code

Everything that runs in the WordPress admin area:

| File | Purpose |
|------|---------|
| `class-admin.php` | Admin initialization, asset loading |
| `views/` | Admin page templates (partials) |

Future additions: settings pages, meta boxes, admin notices.

### `public/` — Front-End Code

Everything that runs on the public-facing site:

| File | Purpose |
|------|---------|
| `class-public.php` | Public initialization, asset loading |
| `views/` | Front-end templates (partials) |

Future additions: shortcodes, widgets, front-end AJAX.

### `includes/` — Shared Utilities

Code used by both admin and public:

| File | Purpose |
|------|---------|
| `functions.php` | Helper functions available everywhere |
| `class-services.php` | Lightweight service registry (centralizes shared instances) |
| `class-template-loader.php` | Theme override-friendly template rendering |
| `ajax/class-ajax.php` | Helper for admin-ajax handlers (nonce, caps, JSON responses) |
| `db/schema.php` | Custom table schema strings for dbDelta() |
| `db/class-db.php` | DB lifecycle (install/upgrade/drop) + query helpers |
| `rest/class-rest.php` | REST routes (namespace + example endpoint) |

Guidelines:

- Only add functions used in multiple places
- Keep functions small and focused
- Prefix all functions with `wp_forever_`

### `templates/` — Theme-Overridable Templates

PHP templates that themes can override. This template ships a working example used by the `[wp_forever_example]` shortcode:
- `templates/shortcodes/example.php`

How template overriding works:

1. Plugin checks if theme has `wp-forever/{template}` (child theme overrides parent)
2. If yes, use theme's version
3. If no, use plugin's default

### `assets/` — Static Assets

```
assets/
├── css/
│   ├── admin.css       # Admin styles
│   └── public.css      # Public styles (conditional)
├── js/
│   ├── admin.js        # Admin scripts
│   └── public.js       # Public scripts (conditional + AJAX example)
└── images/             # Icons, logos, etc.
```

Guidelines:

- No build tools required — files work as-is
- Use `WP_FOREVER_VERSION` for cache busting
- Prefix CSS classes with `wp-forever-`

### `languages/` — Internationalization

```
languages/
└── wp-forever.pot      # Translation template
```

Translators create `.po` files from the `.pot` template. Compiled `.mo` files are loaded by `WP_Forever_I18n`.

---

## Bootstrap Flow

How the plugin initializes (numbered steps):

```
1. WordPress loads wp-forever.php
   ↓
2. ABSPATH check (prevent direct access)
   ↓
3. Constants defined (WP_FOREVER_VERSION, etc.)
   ↓
4. Core classes loaded (activator, deactivator, i18n, plugin)
   ↓
5. Shared includes loaded (functions.php, services, template loader, db/rest/ajax helpers)
   ↓
6. Admin classes loaded (if is_admin())
   ↓
7. Public classes loaded (if front-end or AJAX)
   ↓
8. Activation/deactivation hooks registered
   ↓
9. wp_forever_init() hooked to 'plugins_loaded'
   ↓
10. On 'plugins_loaded': i18n loaded, WP_Forever_Plugin::run()
    ↓
11. Plugin::run() registers admin and public hooks
    ↓
12. Plugin is ready!
```

---

## Naming Conventions

### Classes

| Type | Pattern | Example |
|------|---------|---------|
| Core classes | `WP_Forever_{Name}` | `WP_Forever_Plugin` |
| Admin classes | `WP_Forever_Admin_{Name}` | `WP_Forever_Admin_Settings` |
| Public classes | `WP_Forever_Public_{Name}` | `WP_Forever_Public_Shortcodes` |

### Functions

All functions use the `wp_forever_` prefix:

```php
wp_forever_get_option()
wp_forever_is_debug()
wp_forever_log()
```

### Hooks

All custom hooks use the `wp_forever/` namespace:

```php
// Actions
do_action( 'wp_forever/activated' );
do_action( 'wp_forever/loaded' );

// Filters
apply_filters( 'wp_forever/settings/defaults', $defaults );
apply_filters( 'wp_forever/admin/menu_capability', 'manage_options' );
```

### Options

All options use the `wp_forever_` prefix:

| Option Key | Purpose |
|------------|---------|
| `wp_forever_version` | Installed plugin version |
| `wp_forever_settings` | Main settings array |

### Transients

All transients use the `wp_forever_` prefix:

```php
set_transient( 'wp_forever_api_cache', $data, HOUR_IN_SECONDS );
get_transient( 'wp_forever_api_cache' );
```

### Nonces

All nonces use the `wp_forever_` prefix:

```php
wp_nonce_field( 'wp_forever_save_settings', 'wp_forever_nonce' );
wp_verify_nonce( $_POST['wp_forever_nonce'], 'wp_forever_save_settings' );
```

---

## File Organization

When deciding where to put new code:

| If the code... | Put it in... |
|----------------|--------------|
| Runs only in admin | `admin/` |
| Runs only on front-end | `public/` |
| Is used by both admin and public | `includes/` |
| Manages plugin lifecycle | `core/` |
| Is a reusable template | `templates/` |

---

## Hooks & Filters

### Built-in Extension Points

The plugin provides these hooks for extensibility:

```php
// Fired when plugin activates
do_action( 'wp_forever/activated' );

// Fired when plugin deactivates
do_action( 'wp_forever/deactivated' );

// Fired after plugin initializes
do_action( 'wp_forever/loaded' );
```

### Adding Your Own Hooks

When adding features, provide hooks for extensibility:

```php
// Before processing
do_action( 'wp_forever/before_process', $data );

// Allow filtering data
$data = apply_filters( 'wp_forever/process_data', $data );

// After processing
do_action( 'wp_forever/after_process', $data, $result );
```

---

## Asset Loading

### How Assets Are Enqueued

Admin assets are loaded in `WP_Forever_Admin`:

```php
wp_enqueue_style( 'wp-forever-admin', WP_FOREVER_PLUGIN_URL . 'assets/css/admin.css', array(), WP_FOREVER_VERSION );
wp_enqueue_script( 'wp-forever-admin', WP_FOREVER_PLUGIN_URL . 'assets/js/admin.js', array(), WP_FOREVER_VERSION, true );
```

### Cache Busting

Version numbers ensure browsers load new files after updates:

```php
WP_FOREVER_VERSION  // Used in wp_enqueue_style/script
```

### Conditional Loading

Load assets only where needed:

```php
public function enqueue_styles( string $hook_suffix ): void {
    // Only load on our plugin's pages
    if ( 'toplevel_page_wp-forever' !== $hook_suffix ) {
        return;
    }

    wp_enqueue_style( 'wp-forever-admin', ... );
}
```

---

## Internationalization

### Using Translation Functions

All user-facing strings should be translatable:

```php
// Simple string
__( 'Settings', 'wp-forever' )

// Echo directly
_e( 'Save Changes', 'wp-forever' )

// With escaping
esc_html__( 'Error message', 'wp-forever' )

// With placeholder
sprintf( __( 'Hello, %s', 'wp-forever' ), $name )
```

### Text Domain

The text domain `wp-forever` must:

1. Match the `Text Domain:` header in the main plugin file
2. Be used consistently in all `__()`, `_e()`, etc. calls
3. Match the folder name for WordPress.org translations

### Translation Workflow

1. Use translation functions throughout code
2. Generate `.pot` file with WP-CLI: `wp i18n make-pot . languages/wp-forever.pot`
3. Translators create `.po` files for each language
4. Compile `.po` to `.mo` files
5. `WP_Forever_I18n` loads the appropriate `.mo` file

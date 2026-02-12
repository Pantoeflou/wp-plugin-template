# Database Layer

This folder contains the template's **custom table** scaffolding.

- `schema.php` defines SQL strings for tables used by `dbDelta()`.
- `class-db.php` contains:
  - install/upgrade logic (`install_or_upgrade()`)
  - table name helpers
  - a safe place for query helpers as your plugin grows

## How it works

1. Bump `WP_FOREVER_DB_SCHEMA_VERSION` in `wp-forever.php` whenever you change schema.
2. Update the SQL strings in `schema.php`.
3. On activation/upgrade, `WP_Forever_DB::install_or_upgrade()` runs `dbDelta()`.

## Uninstall behavior

Custom tables are dropped **only** if the user enabled:
**Settings → WP Forever → General → “Delete data on uninstall”**.
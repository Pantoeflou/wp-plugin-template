# AJAX (admin-ajax.php)

This folder contains small helpers for **admin-ajax.php** handlers.

- `class-ajax.php` provides:
  - `require_post()`
  - `verify_nonce( $action, $field = 'nonce' )`
  - `require_cap( $capability )`
  - `send_success()` / `send_error()`

## Where handlers live

For clarity, keep handler methods in the class closest to the UI:

- **Admin UI** handlers live in `admin/` (and should normally check `manage_options`).
- **Public UI** handlers live in `public/` (and should check the minimum capability required).

## Naming

Use action names with a clear prefix:

- `wp_forever_public_ping`
- `wp_forever_admin_dismiss_notice`
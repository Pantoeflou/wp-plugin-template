# WooCommerce Integration

This folder contains **optional** WooCommerce integration code.

- The integration is a **no-op** unless WooCommerce is active.
- Hooks are registered only if the setting is enabled:
  **Settings → WP Forever → Advanced → “Enable WooCommerce Features”.**

Use this as a starting point for:
- product meta
- checkout fields
- order hooks
- subscriptions
- admin reports

Tip: keep Woo-specific code isolated here to avoid accidental hard dependencies.

# Project Layout Reference

This file represents the canonical structure of the plugin.

Kiro must reference this before adding new files.

---

# Root Structure

wp-forever/
├── admin/
├── assets/
├── core/
├── includes/
├── integrations/
├── public/
├── templates/
├── docs/
├── uninstall.php
└── wp-forever.php

---

# Folder Responsibilities

## admin/
Admin UI logic:
- Settings pages
- Notices
- Admin enqueue logic

## public/
Frontend:
- Shortcodes
- Public AJAX
- Public enqueue

## includes/
Shared logic:
- DB
- REST
- AJAX helpers
- Services
- Template loader
- Logger

## integrations/
External platform integrations:
- WooCommerce
- Future integrations

## templates/
Frontend render templates.
Theme override path:
`{theme}/wp-forever/`

## assets/
CSS and JS files.
Only enqueue conditionally.

---

# Structure Rules

1. Do not duplicate logic across admin/public.
2. Shared utilities belong in includes/.
3. Integrations belong in integrations/.
4. Templates must not contain business logic.
5. DB schema must live in includes/db/.

---

If structure changes, update this file.
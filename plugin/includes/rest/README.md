# REST API

This folder contains REST route scaffolding.

- `class-rest.php` registers routes under the namespace `wp-forever/v1`.
- The template includes a working example endpoint:
  - `GET /wp-json/wp-forever/v1/ping`

## Toggling routes

Routes are registered only if the setting is enabled:
**Settings → WP Forever → Advanced → “Enable REST API”.**

## Pattern

- Use `permission_callback` to enforce capabilities / authentication.
- Sanitize request values in callbacks.
- Return `WP_REST_Response` or `WP_Error`.
# WP Forever – Kiro Steering Document

This document defines the non-negotiable principles for developing plugins using the WP Forever template.

Kiro must follow this document strictly.

---

# Core Philosophy

## 1. Simplicity Over Magic

- Prefer readable code over clever abstractions.
- Avoid unnecessary layers, frameworks, or patterns.
- Use WordPress APIs first.
- No dependency injection frameworks.
- No service containers unless justified and approved.

If a solution feels “smart”, simplify it.

---

## 2. Small, Incremental Changes

Every change must:
- Be small in scope.
- Solve one problem.
- Be documented.
- Include verification notes.

Avoid large refactors unless explicitly requested.

---

## 3. Respect the Existing Structure

The current folder structure is intentional.

- Do not restructure folders without strong justification.
- Extend structure only when repeated patterns justify it.
- Update PROJECT_LAYOUT.md if structure changes.

---

## 4. Security Is Mandatory

Every change must consider:

- Capabilities for admin actions.
- Nonces for state-changing requests.
- Sanitization of input.
- Escaping on output.
- Prepared SQL queries.
- Proper REST permission callbacks.

If unsure → secure by default.

---

## 5. Performance Awareness

- Enqueue scripts/styles only where needed.
- Avoid heavy operations on `init`.
- Avoid unnecessary database calls.
- Cache values within request scope when appropriate.
- Do not introduce polling unless justified.

---

## 6. Documentation as Development

As features are added:

- Update relevant docs.
- Add decisions to DECISIONS.md.
- Update PROJECT_LAYOUT.md if structure changes.
- Keep comments clear and purposeful.

Documentation is part of the deliverable.

---

## 7. Modern WordPress Practices

Use:

- Settings API
- REST API (when appropriate)
- Proper enqueueing
- Localization functions
- Theme template overrides where applicable

Avoid:

- Direct database queries without prepare()
- Direct access without ABSPATH check
- Inline script injections
- Globals unless necessary

---

## 8. JavaScript Philosophy

- Prefer vanilla JS.
- Use progressive enhancement.
- Use `wp_localize_script` for data.
- Use nonces for AJAX/REST.
- Avoid frameworks unless explicitly requested.

Keep JS minimal and performant.

---

## 9. UI/UX Standards

- Follow WordPress admin UI patterns.
- Keep UI clean and modern.
- Avoid over-designed interfaces.
- Ensure accessibility basics (labels, descriptions).

---

## 10. Admin Menu Placement

The plugin must support:

- Either a top-level menu
- Or placement under "Tools"

The implementation must allow switching via configuration without major restructuring.

---

This document overrides stylistic preferences.
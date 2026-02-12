# Admin Menu Placement Policy

The plugin supports two modes:

1. Top-level admin menu
2. Submenu under Tools

---

# When to Use Top-Level Menu

- Plugin has multiple screens.
- Plugin is core to site functionality.
- Plugin has dashboard-style UI.

---

# When to Use Tools Submenu

- Utility plugins.
- Single settings page.
- Developer-oriented tools.

---

# Implementation Rule

Menu registration must:

- Be configurable.
- Avoid duplicating logic.
- Use a single registration method.
- Maintain consistent capability checks.

---

Do not hardcode menu placement without configurability.
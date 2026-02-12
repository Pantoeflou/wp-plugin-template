# WP Plugin Kiro Template (Bootstrap Flow)

This repo is a **reusable WordPress plugin starter** designed to work well with **Kiro** and local Docker-based WordPress.

The intended workflow is:

1. Clone the repo
2. Set your plugin name + slug in `bootstrap.json`
3. Run the bootstrap script (fixes paths + renames plugin identifiers)
4. Start local WP via PowerShell scripts
5. Run sanity checks
6. Open in Kiro and confirm MCP + steering/skills are active
7. Create your first spec and start building

---

## Prerequisites

You need these installed locally:

- Python 3.10+ (ensure `python` works in PowerShell)
- Node.js + npm (required for `/mcp` install step)
- Docker Desktop (or compatible Docker engine)
- PowerShell (Windows built-in)

Optional but recommended:
- Git
- Kiro installed and ready

---

## Step-by-step: From clone to working local dev

### 1) Clone the repo

```powershell
git clone <YOUR_REPO_URL>
cd <REPO_FOLDER>
```

---

### 2) Update `bootstrap.json`

Edit `bootstrap.json` in the repo root:

```json
{
  "plugin": {
    "slug": "my-plugin",
    "name": "My Plugin",
    "description": "Short description of what the plugin does (used in header + identity).",
    "plugin_url": "https://example.com/my-plugin",
    "author": "Your Name",
    "author_url": "https://example.com",
    "textdomain": "my-plugin",
    "domain_path": "/languages",
    "version": "0.1.0",
    "requires_wp": "6.0",
    "requires_php": "8.0",
    "license": "GPL-2.0-or-later",
    "license_uri": "https://www.gnu.org/licenses/gpl-2.0.html"
  },
  "dev": {
    "skip_npm": false
  }
}
```

Rules:
- `slug` must be lowercase kebab-case (example: `tabulator`, `geo-shipping`)
- `name` can be any display name (example: `Tabulator`)
- `skip_npm` can be set to `true` if you want to run `npm ci` manually

---

### 3) Run the bootstrap script

This will:
- Update `.kiro/settings/mcp.json` to point to THIS cloned folder
- Run `npm ci` inside `/mcp` (unless skipped)
- Rename plugin references (slug/name/prefixes) across `/plugin`, `/docker`, `/scripts`, etc.

Run:

```powershell
python scripts\bootstrap.py
```

If you prefer one-off flags:

```powershell
python scripts\bootstrap.py --slug tabulator --name "Tabulator"
```

---

### 4) Start local WordPress (PowerShell)

Run the repo’s dev scripts:

```powershell
.\scripts\dev-up.ps1
.\scripts\wp-install.ps1
```

If you need to stop everything later:

```powershell
.\scripts\dev-down.ps1
```

---

### 5) Sanity checks (must pass)

#### A) Containers are running

```powershell
docker ps
```

You should see WordPress + DB containers running.

#### B) Plugin is mounted into the WP container

The plugin is mounted to:

`/wp-content/plugins/<slug>`

Where `<slug>` is whatever you set in `bootstrap.json`.

#### C) WP-CLI can see and activate your plugin

```powershell
docker exec -it <wordpress_container_name> wp plugin list
docker exec -it <wordpress_container_name> wp plugin activate <slug>
```

Plugin should show as `active`.

#### D) MCP dependencies installed

Check locally that:

`mcp/node_modules/` exists

If needed:

```powershell
cd mcp
npm ci
cd ..
```

---

## Kiro setup checks

### 6) Open the project in Kiro

Open the repo root in Kiro (not just `/plugin`).

---

### 7) Confirm steering + skills are picked up

Verify:

- Steering docs are visible
- Skills folder exists and loads
- `.kiro/settings/mcp.json` paths match your cloned repo

---

## Start building: Create your first spec

### 8) Create your first spec

Create:

`specs/001-plugin-goal.md`

Example:

```markdown

# READ FIRST (STRICT RULES):

1) The file .kiro/steering/IDENTITY.md is the single source of truth
   for plugin name, slug, textdomain, prefixes and metadata.

2) Before implementing any feature, perform an IDENTITY CONSISTENCY PASS:
   - Replace ALL remaining references to:
       WP Forever
       wp-forever
       wp_forever
       WP_FOREVER
       Wp_Forever
       WPForever
   - Ensure everything matches IDENTITY.md:
       Plugin header fields
       Class names
       Function prefixes
       Constant prefixes
       Option keys
       Text domain
       Admin menu labels
       Settings page titles
   - Remove any leftover template branding.
   - Do NOT reintroduce template naming.

3) After identity cleanup:
   - Ensure the plugin activates without fatal errors.
   - Do not proceed if activation would fail.

4) MVP PHASE RULES:
   - NO TESTS.
   - NO MOCKS.
   - NO PROPERTY-BASED TESTS.
   - NO TEST HARNESS.
   - Do not scaffold testing infrastructure.
   - Tests will be added in a later phase only after manual verification.

5) ENGINEERING RULES:
   - Work in SMALL incremental steps.
   - Prefer minimal diffs.
   - Avoid large refactors.
   - Avoid introducing new dependencies.
   - Do not execute heavy logic at file load time.
   - Hook logic into plugins_loaded or init.
   - If something fails, fail gracefully with an admin notice.
   - Plugin must remain activation-safe at all times.

--------------------------------------------------

# TASKS:

## Phase 1 – Identity Cleanup
- Perform the identity consistency pass.
- Confirm that no template branding remains.
- Keep changes minimal and safe.

## Phase 2 – MVP Feature
Implement Spec 001:

1) Add a minimal settings page under:
   Settings → <Plugin Name>

2) Save one option in wp_options.

3) Show an admin notice only when the option is empty.

# Deliver this in small, reviewable increments:
- First: settings page scaffold
- Then: option persistence
- Then: admin notice

```

Sample Kiro prompt:

Provided above

---

## Recommended workflow for new plugins

1. Create a new repo from this template
2. Clone locally
3. Edit `bootstrap.json`
4. Run bootstrap
5. Start local WP
6. Verify plugin is active
7. Open in Kiro
8. Write a spec
9. Build iteratively (commit often)

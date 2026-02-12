# WP Forever - New Plugin Checklist

Follow this checklist to create a new plugin from this template.

---

## 1. Copy Template

Clone or copy the template directory:

```bash
# Option A: Copy locally
cp -r wp-forever my-plugin

# Option B: Clone from repo
git clone https://github.com/your-username/wp-forever.git my-plugin
cd my-plugin
rm -rf .git  # Remove template's git history
git init     # Start fresh
```

---

## 2. Rename Plugin

### 2.1 Rename Files and Folders

- [ ] Rename root folder: `wp-forever/` → `my-plugin/`
- [ ] Rename main plugin file: `wp-forever.php` → `my-plugin.php`

### 2.2 Update Plugin Header

Edit your main plugin file (`my-plugin.php`):

- [ ] `Plugin Name:` → Your plugin's display name
- [ ] `Plugin URI:` → Your plugin's website
- [ ] `Description:` → What your plugin does
- [ ] `Version:` → `0.1.0` (or your starting version)
- [ ] `Author:` → Your name
- [ ] `Author URI:` → Your website
- [ ] `Text Domain:` → `my-plugin` (matches folder name)

### 2.3 Find and Replace

Use your editor's find/replace (case-sensitive):

| Find | Replace With | Files Affected |
|------|--------------|----------------|
| `wp-forever` | `my-plugin` | All `.php` files, `.pot` file |
| `WP_FOREVER_` | `MY_PLUGIN_` | All `.php` files |
| `wp_forever_` | `my_plugin_` | All `.php` files |
| `WP_Forever_` | `My_Plugin_` | All `.php` files |
| `wp_forever/` | `my_plugin/` | All `.php` files (hook namespace) |
| `WP Forever` | `My Plugin` | Documentation, comments |
| `wp-forever-` | `my-plugin-` | CSS/JS handles, CSS classes |
| `wpForever` | `myPlugin` | JavaScript object name |

**Checklist for find/replace:**

- [ ] `wp-forever` → `my-plugin` (text domain)
- [ ] `WP_FOREVER_` → `MY_PLUGIN_` (constants)
- [ ] `wp_forever_` → `my_plugin_` (function prefix)
- [ ] `WP_Forever_` → `My_Plugin_` (class prefix)
- [ ] `wp_forever/` → `my_plugin/` (hook namespace)
- [ ] `wp-forever-` → `my-plugin-` (CSS/JS handles)
- [ ] `wpForever` → `myPlugin` (JS object)

### 2.4 Update Translation File

- [ ] Rename `languages/wp-forever.pot` → `languages/my-plugin.pot`
- [ ] Update project name in `.pot` file header

---

## 3. Update Version

In your main plugin file:

- [ ] Set `Version:` to `0.1.0`
- [ ] Set `MY_PLUGIN_VERSION` constant to `'0.1.0'`

---

## 4. Test Activation

### 4.1 Install Plugin

- [ ] Copy your plugin folder to `wp-content/plugins/`
- [ ] Go to WordPress Admin → Plugins
- [ ] Find your plugin in the list

### 4.2 Verify Plugin Information

- [ ] Plugin name displays correctly
- [ ] Description displays correctly
- [ ] Author name and link are correct

### 4.3 Activate Plugin

- [ ] Click "Activate"
- [ ] No PHP errors or warnings
- [ ] No white screen of death

### 4.4 Check Database

- [ ] Option `my_plugin_version` exists (check with WP-CLI or phpMyAdmin)

```bash
wp option get my_plugin_version
# Should return: 0.1.0
```

---

## 5. Verify Requirements

### 5.1 Test PHP Version Check

If possible, test on PHP < 8.0:

- [ ] Plugin refuses to activate
- [ ] Error message shows PHP requirement

### 5.2 Test WordPress Version Check

If possible, test on WordPress < 6.0:

- [ ] Plugin refuses to activate
- [ ] Error message shows WordPress requirement

---

## 6. Verify Deactivation

- [ ] Deactivate the plugin
- [ ] No errors during deactivation
- [ ] Plugin can be reactivated

---

## 7. Verify Uninstall

**Warning:** This deletes plugin data!

- [ ] Delete the plugin via WordPress Admin
- [ ] Option `my_plugin_version` is removed
- [ ] Option `my_plugin_settings` is removed (if it exists)

---

## 8. Commit Initial State

Create your first commit with the clean, renamed template:

```bash
git add .
git commit -m "Initial commit from WP Forever template"
```

---

## Quick Reference: File Locations

After renaming, you'll have:

| File | Purpose |
|------|---------|
| `my-plugin.php` | Main plugin file |
| `uninstall.php` | Cleanup on deletion |
| `core/class-plugin.php` | Main orchestrator |
| `core/class-activator.php` | Activation logic |
| `core/class-deactivator.php` | Deactivation logic |
| `core/class-i18n.php` | Translation loading |
| `admin/class-admin.php` | Admin functionality |
| `public/class-public.php` | Front-end functionality |
| `includes/functions.php` | Helper functions |

---

## Common Issues

### "Plugin file does not exist"

The main plugin file name must match the folder name:

```
my-plugin/
└── my-plugin.php  ✓

my-plugin/
└── wp-forever.php  ✗ (forgot to rename)
```

### "Class not found" errors

Check that class names were updated in all files:

- Main plugin file `require_once` statements
- `register_activation_hook()` callback
- `register_deactivation_hook()` callback

### Text domain mismatch

Ensure the text domain matches everywhere:

- Plugin header `Text Domain:`
- All `__()`, `_e()`, etc. calls
- The `.pot` filename

---

## Next Steps

After completing this checklist:

1. **Add your features** — Start in `admin/` or `public/`
2. **Write tests** — Add PHPUnit tests (Phase 5)
3. **Generate translations** — `wp i18n make-pot . languages/my-plugin.pot`
4. **Set up CI** — Add GitHub Actions (Phase 5)

Happy coding!

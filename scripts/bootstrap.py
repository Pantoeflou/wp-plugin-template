#!/usr/bin/env python3
"""
Bootstrap this repo after cloning.

What it does:
1) Updates .kiro/settings/mcp.json to point at THIS cloned repo location (no hardcoded paths).
2) Runs `npm ci` in ./mcp (installs MCP dependencies).
3) Renames the WordPress plugin identifiers from the template values (wp-forever / WP Forever)
   to your new slug + display name across:
   - ./plugin (PHP, CSS, JS, MD)
   - ./docker/docker-compose.yml
   - ./scripts/*.ps1

Usage (from repo root):
  python scripts/bootstrap.py --slug my-plugin --name "My Plugin"
"""

from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import subprocess
import sys
from pathlib import Path
from typing import Iterable


REPO_ROOT = Path(__file__).resolve().parents[1]

# Template identifiers currently in your repo
TEMPLATE = {
    "display_name": "WP Forever",
    "slug": "wp-forever",
    "slug_us": "wp_forever",
    "const": "WP_FOREVER",
    "class_prefix": "Wp_Forever",
    "camel": "WPForever",
}


TEXT_EXTS = {
    ".php", ".md", ".txt",
    ".js", ".mjs", ".css",
    ".json", ".yml", ".yaml",
    ".ps1",
}


def is_windows() -> bool:
    return os.name == "nt"


def slug_to_us(slug: str) -> str:
    return slug.replace("-", "_")


def slug_to_const(slug: str) -> str:
    return slug_to_us(slug).upper()


def slug_to_class_prefix(slug: str) -> str:
    # my-plugin -> My_Plugin
    parts = slug.replace("-", "_").split("_")
    return "_".join(p[:1].upper() + p[1:] for p in parts if p)


def slug_to_camel(slug: str) -> str:
    # my-plugin -> MyPlugin
    parts = re.split(r"[-_]+", slug)
    return "".join(p[:1].upper() + p[1:] for p in parts if p)


def iter_text_files(base: Path) -> Iterable[Path]:
    for p in base.rglob("*"):
        if not p.is_file():
            continue
        if p.suffix.lower() in TEXT_EXTS:
            # Skip lockfiles? (we DO want to update package-lock only if it has template strings;
            # but normally it shouldn't.)
            yield p


def safe_read_text(p: Path) -> str | None:
    try:
        return p.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        return None


def safe_write_text(p: Path, s: str) -> None:
    p.write_text(s, encoding="utf-8", newline="\n")


def replace_all(content: str, replacements: list[tuple[str, str]]) -> tuple[str, bool]:
    original = content
    for a, b in replacements:
        content = content.replace(a, b)
    return content, content != original


def update_mcp_json(repo_root: Path) -> None:
    p = repo_root / ".kiro" / "settings" / "mcp.json"
    if not p.exists():
        print(f"⚠️  Skipping MCP config (not found): {p}")
        return

    data = json.loads(p.read_text(encoding="utf-8"))

    server_path = (repo_root / "mcp" / "server.mjs").resolve()
    fs_root = repo_root.resolve()

    # Kiro expects strings in Windows form when on Windows.
    # We’ll always write platform-native paths.
    if is_windows():
        server_arg = str(server_path)
        fs_arg = str(fs_root)
    else:
        server_arg = server_path.as_posix()
        fs_arg = fs_root.as_posix()

    # Update known keys if present; keep structure intact.
    mcp_servers = data.get("mcpServers", {})

    if "kiro-local-wp" in mcp_servers:
        mcp_servers["kiro-local-wp"]["command"] = "node"
        mcp_servers["kiro-local-wp"]["args"] = [server_arg]

    if "filesystem" in mcp_servers:
        mcp_servers["filesystem"]["command"] = "npx"
        # Preserve other args; last arg is root path in your current template
        args = mcp_servers["filesystem"].get("args", [])
        if args:
            args[-1] = fs_arg
        else:
            args = ["-y", "@modelcontextprotocol/server-filesystem", fs_arg]
        mcp_servers["filesystem"]["args"] = args

    data["mcpServers"] = mcp_servers

    p.write_text(json.dumps(data, indent=2), encoding="utf-8")
    print(f"✅ Updated MCP config paths: {p}")


def npm_ci(repo_root: Path) -> None:
    mcp_dir = repo_root / "mcp"
    if not (mcp_dir / "package.json").exists():
        print("⚠️  Skipping npm ci (no mcp/package.json found).")
        return

    npm = "npm.cmd" if is_windows() else "npm"
    print("▶ Running npm ci in ./mcp ...")
    try:
        subprocess.run([npm, "ci"], cwd=str(mcp_dir), check=True)
        print("✅ npm ci complete")
    except FileNotFoundError:
        print("❌ npm not found. Install Node.js (which includes npm), then rerun bootstrap.")
        raise
    except subprocess.CalledProcessError as e:
        print("❌ npm ci failed.")
        raise e


def rename_plugin(repo_root: Path, new_slug: str, new_name: str) -> None:
    new = {
        "display_name": new_name,
        "slug": new_slug,
        "slug_us": slug_to_us(new_slug),
        "const": slug_to_const(new_slug),
        "class_prefix": slug_to_class_prefix(new_slug),
        "camel": slug_to_camel(new_slug),
    }

    replacements = [
        (TEMPLATE["display_name"], new["display_name"]),
        (TEMPLATE["slug"], new["slug"]),
        (TEMPLATE["slug_us"], new["slug_us"]),
        (TEMPLATE["const"], new["const"]),
        (TEMPLATE["class_prefix"], new["class_prefix"]),
        (TEMPLATE["camel"], new["camel"]),
    ]

    # 1) Content replacements across repo (focused on relevant dirs)
    targets = [
        repo_root / "plugin",
        repo_root / "docker",
        repo_root / "scripts",
        repo_root / ".kiro",
        repo_root / "mcp",
    ]

    changed_files = 0
    for base in targets:
        if not base.exists():
            continue
        for f in iter_text_files(base):
            text = safe_read_text(f)
            if text is None:
                continue
            updated, changed = replace_all(text, replacements)
            if changed:
                safe_write_text(f, updated)
                changed_files += 1

    print(f"✅ Rewrote identifiers in {changed_files} files")

    # 2) Rename main plugin file: plugin/wp-forever.php -> plugin/<slug>.php
    old_main = repo_root / "plugin" / f"{TEMPLATE['slug']}.php"
    new_main = repo_root / "plugin" / f"{new['slug']}.php"
    if old_main.exists():
        if new_main.exists():
            print(f"⚠️  Target main plugin file already exists: {new_main} (skipping rename)")
        else:
            old_main.rename(new_main)
            print(f"✅ Renamed main plugin file: {old_main.name} -> {new_main.name}")

    # 3) Ensure docker mount path matches slug (just in case template text wasn’t present somewhere)
    compose = repo_root / "docker" / "docker-compose.yml"
    if compose.exists():
        txt = compose.read_text(encoding="utf-8")
        # Force mount destination to /wp-content/plugins/<slug>
        txt2 = re.sub(
            r"/wp-content/plugins/[A-Za-z0-9\-_]+(?::delegated)?",
            f"/wp-content/plugins/{new['slug']}:delegated",
            txt,
        )
        if txt2 != txt:
            compose.write_text(txt2, encoding="utf-8", newline="\n")
            print("✅ Normalized docker-compose plugin mount path")

    # 4) Rename the plugin folder inside container? (We mount ./plugin into /wp-content/plugins/<slug>)
    # No local folder rename needed; WordPress uses the directory name inside container.
    # We already mount to /.../<slug>, so that directory will exist.

    print("✅ Plugin rename complete")


def validate_slug(slug: str) -> None:
    if not re.fullmatch(r"[a-z0-9]+(?:-[a-z0-9]+)*", slug):
        raise ValueError("Slug must be lowercase kebab-case, e.g. my-plugin or geo-shipping")


def load_bootstrap_json(repo_root: Path) -> dict:
    p = repo_root / "bootstrap.json"
    if not p.exists():
        return {}
    return json.loads(p.read_text(encoding="utf-8"))

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--slug", help="Plugin slug (kebab-case), e.g. my-plugin")
    ap.add_argument("--name", help='Plugin display name, e.g. "My Plugin"')
    ap.add_argument("--skip-npm", action="store_true", help="Skip npm ci step")
    args = ap.parse_args()

    cfg = load_bootstrap_json(REPO_ROOT)

    slug = args.slug or cfg.get("slug")
    name = args.name or cfg.get("name")
    skip_npm = bool(args.skip_npm or cfg.get("skip_npm", False))

    if not slug or not name:
        print("❌ Missing slug/name. Provide via flags or bootstrap.json.")
        print('   Example: python scripts/bootstrap.py --slug tabulator --name "Tabulator"')
        return 2

    validate_slug(slug)

    print(f"Repo root: {REPO_ROOT}")
    update_mcp_json(REPO_ROOT)

    if not skip_npm:
        npm_ci(REPO_ROOT)

    rename_plugin(REPO_ROOT, slug, name)

    print("\nAll done ✅")
    print("Next steps:")
    print("  1) docker compose up -d   (or run scripts/dev-up.ps1)")
    print("  2) scripts/wp-install.ps1")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
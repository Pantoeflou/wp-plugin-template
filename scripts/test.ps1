$ErrorActionPreference = "Stop"

# Ensure containers are up
Write-Host ">> Checking if kiro_wp container is running..."
$containers = docker ps --format '{{.Names}}'
if ($containers -notmatch 'kiro_wp') {
  throw "kiro_wp container is not running"
}

Write-Host ""
Write-Host "== Plugin sanity checks =="

# 1) PHP syntax check (for all php files in plugin)
Write-Host ">> Checking PHP syntax..."
docker exec kiro_wp sh -lc "find /var/www/html/wp-content/plugins/wp-forever -name '*.php' -print0 | xargs -0 -n1 php -l"
if (-not $?) { throw "PHP syntax check failed" }

# 2) Confirm WordPress responds
Write-Host ">> Checking PHP execution..."
docker exec kiro_wp sh -lc "php -r 'echo \"PHP_OK\n\";'"
if (-not $?) { throw "PHP execution check failed" }

# 3) Confirm plugin is active
Write-Host ">> Checking if plugin is active..."
docker exec kiro_wp_cli sh -lc "wp plugin is-active wp-forever --path=/var/www/html"
if (-not $?) { throw "Plugin is not active" }

Write-Host ""
Write-Host "✅ All checks passed."

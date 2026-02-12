$ErrorActionPreference = "Stop"

cd (Join-Path $PSScriptRoot "..\docker")

# Wait for WordPress container to be reachable
Write-Host "Waiting for WordPress container..."
Start-Sleep -Seconds 3

# Ensure wp-cli container is running (it sleeps infinity)
docker compose up -d wpcli > $null 2>&1

# Install WordPress if not installed yet
$installed = docker exec kiro_wp_cli sh -lc "wp core is-installed --path=/var/www/html >/dev/null 2>&1; echo $?"
if ($installed -ne "0") {
  Write-Host "Installing WordPress..."
  docker exec kiro_wp_cli sh -lc "wp core install --path=/var/www/html --url=http://localhost:8080 --title='Kiro Local WP' --admin_user=admin --admin_password=admin --admin_email=admin@example.com"
} else {
  Write-Host "WordPress already installed."
}

Write-Host "Activating plugin..."
docker exec kiro_wp_cli sh -lc "wp plugin activate wp-forever --path=/var/www/html"

Write-Host ""
Write-Host "Done. Open: http://localhost:8080/wp-admin"
Write-Host "Login: admin / admin"
$ErrorActionPreference = "Stop"

cd (Join-Path $PSScriptRoot "..\docker")

Write-Host "Deactivating WP Forever plugin..."
docker exec kiro_wp_cli sh -lc "wp plugin deactivate wp-forever --path=/var/www/html"

Write-Host "✅ Plugin deactivated successfully."

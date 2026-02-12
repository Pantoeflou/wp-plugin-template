$ErrorActionPreference = "Stop"

cd (Join-Path $PSScriptRoot "..\docker")

Write-Host "Activating WP Forever plugin..."
docker exec kiro_wp_cli sh -lc "wp plugin activate wp-forever --path=/var/www/html"

Write-Host "✅ Plugin activated successfully."

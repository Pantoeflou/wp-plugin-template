$ErrorActionPreference = "Stop"

cd (Join-Path $PSScriptRoot "..\docker")

docker compose up -d

Write-Host ""
Write-Host "WordPress starting on: http://localhost:8080"
Write-Host "Next: run scripts\wp-install.ps1"
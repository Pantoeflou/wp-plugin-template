$ErrorActionPreference = "Stop"

cd (Join-Path $PSScriptRoot "..\docker")

docker compose down -v

Write-Host "Reset complete (volumes removed). Run scripts\dev-up.ps1 again."
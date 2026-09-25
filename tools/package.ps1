# Builds dist\workflow-dashboard.zip on Windows with only the files the server needs.
# Run from anywhere:  powershell -ExecutionPolicy Bypass -File <project folder>\tools\package.ps1
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root
$dist = Join-Path $root 'dist'
$zip = Join-Path $dist 'workflow-dashboard.zip'
New-Item -ItemType Directory -Force -Path $dist | Out-Null
if (Test-Path $zip) { Remove-Item $zip }

# Copy into a staging folder first, so config.php and saved sessions never end up in the zip.
$stage = Join-Path $env:TEMP ('workflow-package-' + [guid]::NewGuid())
New-Item -ItemType Directory -Path $stage | Out-Null
foreach ($item in 'index.php', 'install.php', 'config.sample.php', '.htaccess', 'app', 'pages', 'partials', 'assets', 'database') {
    Copy-Item -Path (Join-Path $root $item) -Destination $stage -Recurse -Force
}
New-Item -ItemType Directory -Path (Join-Path $stage 'storage') | Out-Null
Copy-Item -Path (Join-Path $root 'storage\.htaccess') -Destination (Join-Path $stage 'storage') -Force

Compress-Archive -Path (Join-Path $stage '*') -DestinationPath $zip
Remove-Item -Recurse -Force $stage
Write-Host "Created $zip"

# Hassan IDE - Copy Builds to Downloads Script
# ينقل ملفات البناء إلى مجلد التحميلات العام

param(
    [string]$Version = "1.108.0"
)

$ErrorActionPreference = "Stop"
$ROOT = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$DOWNLOADS_DIR = Join-Path $ROOT "public_html\downloads"
$BUILD_DIR = Join-Path $ROOT ".build"

Write-Host "╔════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   Hassan IDE - Copy Builds to Downloads            ║" -ForegroundColor Cyan
Write-Host "║   نسخ ملفات البناء إلى مجلد التحميلات              ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Create downloads directory if not exists
if (-not (Test-Path $DOWNLOADS_DIR)) {
    New-Item -ItemType Directory -Path $DOWNLOADS_DIR -Force | Out-Null
    Write-Host "✓ Created downloads directory" -ForegroundColor Green
}

# Function to copy and rename file
function Copy-BuildFile {
    param(
        [string]$Source,
        [string]$Destination
    )
    
    if (Test-Path $Source) {
        Copy-Item -Path $Source -Destination $Destination -Force
        $size = [math]::Round((Get-Item $Destination).Length / 1MB, 2)
        Write-Host "  ✓ Copied: $(Split-Path -Leaf $Destination) ($size MB)" -ForegroundColor Green
        return $true
    } else {
        Write-Host "  ⚠ Not found: $Source" -ForegroundColor Yellow
        return $false
    }
}

# Windows x64
Write-Host "`n[Windows x64]" -ForegroundColor Blue
$winX64Dir = Join-Path (Split-Path -Parent $ROOT) "VSCode-win32-x64"
if (Test-Path $winX64Dir) {
    # Create ZIP
    $zipPath = Join-Path $DOWNLOADS_DIR "HassanIDE-win32-x64-$Version.zip"
    Write-Host "  Creating ZIP archive..." -ForegroundColor Gray
    Compress-Archive -Path "$winX64Dir\*" -DestinationPath $zipPath -Force
    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "  ✓ Created: HassanIDE-win32-x64-$Version.zip ($size MB)" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Build not found: $winX64Dir" -ForegroundColor Yellow
}

# Windows x64 Installer
$winX64Installer = Join-Path $BUILD_DIR "win32-x64\system-setup\HassanIDESetup.exe"
if (Test-Path $winX64Installer) {
    Copy-BuildFile -Source $winX64Installer -Destination (Join-Path $DOWNLOADS_DIR "HassanIDESetup-x64-$Version.exe")
} else {
    $winX64InstallerUser = Join-Path $BUILD_DIR "win32-x64\user-setup\HassanIDEUserSetup.exe"
    Copy-BuildFile -Source $winX64InstallerUser -Destination (Join-Path $DOWNLOADS_DIR "HassanIDESetup-x64-$Version.exe")
}

# Windows ARM64
Write-Host "`n[Windows ARM64]" -ForegroundColor Blue
$winArm64Dir = Join-Path (Split-Path -Parent $ROOT) "VSCode-win32-arm64"
if (Test-Path $winArm64Dir) {
    $zipPath = Join-Path $DOWNLOADS_DIR "HassanIDE-win32-arm64-$Version.zip"
    Write-Host "  Creating ZIP archive..." -ForegroundColor Gray
    Compress-Archive -Path "$winArm64Dir\*" -DestinationPath $zipPath -Force
    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "  ✓ Created: HassanIDE-win32-arm64-$Version.zip ($size MB)" -ForegroundColor Green
}

# macOS x64
Write-Host "`n[macOS x64]" -ForegroundColor Blue
$macX64Dir = Join-Path (Split-Path -Parent $ROOT) "VSCode-darwin-x64"
if (Test-Path $macX64Dir) {
    $zipPath = Join-Path $DOWNLOADS_DIR "HassanIDE-darwin-x64-$Version.zip"
    Write-Host "  Creating ZIP archive..." -ForegroundColor Gray
    Compress-Archive -Path "$macX64Dir\*" -DestinationPath $zipPath -Force
    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "  ✓ Created: HassanIDE-darwin-x64-$Version.zip ($size MB)" -ForegroundColor Green
}

# macOS ARM64
Write-Host "`n[macOS ARM64]" -ForegroundColor Blue
$macArm64Dir = Join-Path (Split-Path -Parent $ROOT) "VSCode-darwin-arm64"
if (Test-Path $macArm64Dir) {
    $zipPath = Join-Path $DOWNLOADS_DIR "HassanIDE-darwin-arm64-$Version.zip"
    Write-Host "  Creating ZIP archive..." -ForegroundColor Gray
    Compress-Archive -Path "$macArm64Dir\*" -DestinationPath $zipPath -Force
    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "  ✓ Created: HassanIDE-darwin-arm64-$Version.zip ($size MB)" -ForegroundColor Green
}

# Linux x64
Write-Host "`n[Linux x64]" -ForegroundColor Blue
$linuxX64Dir = Join-Path (Split-Path -Parent $ROOT) "VSCode-linux-x64"
if (Test-Path $linuxX64Dir) {
    $tarPath = Join-Path $DOWNLOADS_DIR "hassanide-linux-x64-$Version.tar.gz"
    Write-Host "  Note: TAR.GZ creation requires Linux or WSL" -ForegroundColor Gray
}

# Linux DEB
$linuxDeb = Get-ChildItem -Path $BUILD_DIR -Filter "*.deb" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
if ($linuxDeb) {
    Copy-BuildFile -Source $linuxDeb.FullName -Destination (Join-Path $DOWNLOADS_DIR "hassanide_${Version}_amd64.deb")
}

# Linux RPM
$linuxRpm = Get-ChildItem -Path $BUILD_DIR -Filter "*.rpm" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
if ($linuxRpm) {
    Copy-BuildFile -Source $linuxRpm.FullName -Destination (Join-Path $DOWNLOADS_DIR "hassanide-${Version}.x86_64.rpm")
}

# Summary
Write-Host "`n╔════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║              Copy Complete!                        ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host "`nFiles in downloads directory:" -ForegroundColor Cyan
Get-ChildItem $DOWNLOADS_DIR -File | ForEach-Object {
    $size = [math]::Round($_.Length / 1MB, 2)
    Write-Host "  - $($_.Name) ($size MB)"
}

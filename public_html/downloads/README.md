# مجلد التحميلات - Hassan IDE

هذا المجلد يحتوي على ملفات التثبيت لـ Hassan IDE.

## الملفات المتوقعة

### Windows
- `HassanIDESetup-x64.exe` - مثبّت Windows 64-bit
- `HassanIDESetup-arm64.exe` - مثبّت Windows ARM64
- `HassanIDE-win32-x64.zip` - نسخة محمولة

### macOS
- `HassanIDE-darwin-x64.zip` - Intel Mac
- `HassanIDE-darwin-arm64.zip` - Apple Silicon (M1/M2/M3)
- `HassanIDE-darwin-universal.dmg` - نسخة موحدة

### Linux
- `hassanide_amd64.deb` - Ubuntu/Debian
- `hassanide_x86_64.rpm` - Fedora/RHEL
- `hassanide-linux-x64.tar.gz` - نسخة عامة

## ملف الإصدارات

يتم تحديث ملف `versions.json` تلقائياً عند رفع إصدار جديد.

```json
{
  "latest": "1.0.0",
  "windows": {
    "x64": "HassanIDESetup-x64.exe",
    "arm64": "HassanIDESetup-arm64.exe"
  },
  "darwin": {
    "x64": "HassanIDE-darwin-x64.zip",
    "arm64": "HassanIDE-darwin-arm64.zip"
  },
  "linux": {
    "deb": "hassanide_amd64.deb",
    "rpm": "hassanide_x86_64.rpm"
  }
}
```

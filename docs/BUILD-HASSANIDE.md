# دليل بناء Hassan IDE

## الملفات المعدّلة

تم تعديل الملفات التالية لتحويل VS Code إلى Hassan IDE:

### 1. ملفات التكوين الأساسية

| الملف | الوصف |
|-------|-------|
| `product.json` | اسم التطبيق، معرفات النظام، الروابط |
| `package.json` | اسم الحزمة، الناشر، أوامر البناء |

### 2. ملفات الهوية البصرية

| الملف | الوصف |
|-------|-------|
| `resources/hassanide/` | الشعارات والأيقونات بصيغة SVG |
| `resources/linux/hassanide.desktop` | إعدادات سطح المكتب لـ Linux |

### 3. ملفات البناء

| الملف | الوصف |
|-------|-------|
| `build/win32/hassanide.iss` | تكوين مثبّت Windows |
| `build/win32/i18n/messages.ar.isl` | ترجمة عربية للمثبّت |
| `build/win32/i18n/Default.ar.isl` | ملف لغة عربية لـ Inno Setup |
| `build/lib/hassanide-config.mjs` | تكوين البناء |
| `scripts/build-hassanide.js` | سكريبت بناء التطبيق |

### 4. ملفات الوثائق

| الملف | الوصف |
|-------|-------|
| `HASSANIDE.md` | دليل المستخدم |
| `LICENSE-HASSANIDE.txt` | اتفاقية الترخيص |

---

## خطوات البناء

### المتطلبات الأساسية

```bash
# تثبيت Node.js 18+
# تثبيت Python 3+
# تثبيت Visual Studio Build Tools (Windows)
```

### 1. تثبيت التبعيات

```bash
npm install
```

### 2. بناء التطبيق

```bash
# بناء لجميع المنصات
npm run build-hassanide

# أو لمنصة محددة
npm run build-hassanide-win    # Windows فقط
npm run build-hassanide-mac    # macOS فقط (يحتاج Mac)
npm run build-hassanide-linux  # Linux فقط (يحتاج Linux)
```

### 3. مواقع الإخراج

| المنصة | المسار |
|--------|-------|
| Windows (ملفات) | `../VSCode-win32-x64/` |
| Windows (مثبّت) | `.build/win32-x64/` |
| macOS | `../VSCode-darwin-x64/` |
| Linux (ملفات) | `../VSCode-linux-x64/` |
| Linux (.deb/.rpm) | `.build/linux/` |

---

## تحويل الأيقونات

### لـ Windows (ICO)

يجب تحويل `resources/hassanide/icon-square.svg` إلى `resources/win32/hassanide.ico`:

```bash
# باستخدام ImageMagick
convert -background none resources/hassanide/icon-square.svg -define icon:auto-resize=256,128,64,48,32,16 resources/win32/hassanide.ico
```

### لـ macOS (ICNS)

```bash
# باستخدام iconutil على Mac
mkdir hassanide.iconset
# إنشاء صور بأحجام مختلفة
iconutil -c icns hassanide.iconset -o resources/darwin/hassanide.icns
```

### لـ Linux (PNG)

```bash
# تحويل SVG إلى PNG
convert -background none resources/hassanide/icon-square.svg -resize 512x512 resources/linux/hassanide.png
```

---

## الباقات والميزات

### Starter (مجاني)
- المحرر الأساسي
- Terminal
- Git الأساسي
- 5 إضافات
- Pack واحد

### Pro (29 ر.س/شهر)
- جميع ميزات Starter
- AI Assistant
- Templates
- Cloud Sync
- جميع Packs
- إضافات غير محدودة
- تحديثات تلقائية
- Hassan Panel

### Teams (99 ر.س/شهر)
- جميع ميزات Pro
- 5-10 مستخدمين
- لوحة تحكم الفريق
- صلاحيات
- دعم أولوية

---

## الرفع للموقع

بعد البناء، ارفع الملفات التالية:

1. **Windows**: `HassanIDESetup-x64.exe`
2. **macOS**: `HassanIDE-darwin-x64.zip` و `HassanIDE-darwin-arm64.zip`
3. **Linux**: `hassanide_x64.deb` و `hassanide_x64.rpm`

إلى مجلد التحميلات في موقعك: `public_html/downloads/`

---

## ملاحظات مهمة

1. **الأيقونات**: يجب تحويل ملفات SVG إلى ICO/ICNS/PNG قبل البناء الفعلي
2. **التوقيع**: للإنتاج، تحتاج شهادة Code Signing لـ Windows و macOS
3. **الترخيص**: تأكد من تكوين API الترخيص في الخادم قبل الإصدار

# Hassan IDE - محرر الأكواد العربي

<p align="center">
  <img src="resources/hassanide/logo.svg" alt="Hassan IDE Logo" width="200">
</p>

<p align="center">
  <strong>بيئة التطوير المتكاملة الاحترافية للمطورين العرب</strong>
</p>

<p align="center">
  <a href="https://hassanide.com">الموقع الرسمي</a> •
  <a href="https://hassanide.com/download">تحميل</a> •
  <a href="https://hassanide.com/pricing">الباقات</a> •
  <a href="https://hassanide.com/support">الدعم</a>
</p>

---

## 🎯 نظرة عامة

Hassan IDE هو محرر أكواد احترافي مبني على أساس VS Code المفتوح المصدر، مع إضافات وتحسينات خاصة للمطورين العرب.

## ✨ المميزات

### 🆓 باقة Starter (مجانية)
- ✅ المحرر الأساسي الكامل
- ✅ Terminal مدمج
- ✅ تكامل Git الأساسي
- ✅ 5 إضافات
- ✅ Pack واحد (Web أو Python)

### 💎 باقة Pro (29 ر.س/شهر)
- ✅ جميع مميزات Starter
- ✅ مساعد AI ذكي
- ✅ قوالب جاهزة
- ✅ مزامنة سحابية
- ✅ جميع الـ Packs
- ✅ إضافات غير محدودة
- ✅ تحديثات تلقائية
- ✅ Hassan Panel كامل
- ✅ دعم بالإيميل

### 🏢 باقة Teams (99 ر.س/شهر)
- ✅ جميع مميزات Pro
- ✅ 5-10 مستخدمين
- ✅ لوحة تحكم الفريق
- ✅ صلاحيات وسياسات
- ✅ دعم أولوية (24 ساعة)
- ✅ فاتورة رسمية

## 🚀 التثبيت

### Windows
```bash
# تحميل المثبّت
curl -L https://hassanide.com/download/windows -o HassanIDESetup.exe

# تشغيل المثبّت
./HassanIDESetup.exe
```

### macOS
```bash
# تحميل الملف
curl -L https://hassanide.com/download/mac -o HassanIDE.dmg

# فتح وتثبيت
open HassanIDE.dmg
```

### Linux (Ubuntu/Debian)
```bash
# تحميل ملف .deb
curl -L https://hassanide.com/download/linux-deb -o hassanide.deb

# تثبيت
sudo dpkg -i hassanide.deb
```

### Linux (Fedora/RHEL)
```bash
# تحميل ملف .rpm
curl -L https://hassanide.com/download/linux-rpm -o hassanide.rpm

# تثبيت
sudo rpm -i hassanide.rpm
```

## 🔑 تفعيل الترخيص

1. افتح Hassan IDE
2. اضغط `Ctrl+Shift+P` (أو `Cmd+Shift+P` على Mac)
3. اكتب "Activate License"
4. الصق مفتاح الترخيص الخاص بك
5. تم التفعيل! 🎉

## 🛠️ البناء من المصدر

### المتطلبات
- Node.js 18+
- npm 8+
- Python 3+
- Visual Studio Build Tools (Windows)
- Xcode Command Line Tools (macOS)

### خطوات البناء

```bash
# استنساخ المستودع
git clone https://github.com/hassantech/hassanide.git
cd hassanide

# تثبيت التبعيات
npm install

# بناء لجميع المنصات
npm run build-hassanide

# أو بناء لمنصة محددة
npm run build-hassanide-win    # Windows
npm run build-hassanide-mac    # macOS
npm run build-hassanide-linux  # Linux
```

## 📁 هيكل المشروع

```
hassanide/
├── src/                    # الكود المصدري
│   └── vs/
│       └── platform/
│           └── license/    # نظام الترخيص
├── resources/
│   └── hassanide/          # أيقونات وشعارات
├── build/                  # سكريبتات البناء
├── scripts/                # سكريبتات مساعدة
├── public_html/            # ملفات الموقع
└── docs/                   # التوثيق
```

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى قراءة دليل المساهمة قبل إرسال Pull Request.

## 📄 الترخيص

Hassan IDE هو برنامج مملوك. راجع [LICENSE](LICENSE.txt) للتفاصيل.

## 📞 التواصل

- 🌐 الموقع: [hassanide.com](https://hassanide.com)
- 📧 الدعم: support@hassanide.com
- 🐦 تويتر: [@HassanIDE](https://twitter.com/HassanIDE)

---

<p align="center">
  صُنع بـ ❤️ بواسطة <a href="https://hassantech.com">Hassan Tech</a>
</p>

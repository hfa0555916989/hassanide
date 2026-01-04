# دليل النشر - HassanIDE

## المتطلبات

### الخادم (Website)
- PHP 8.0+
- MySQL 8.0+
- SSL Certificate
- Hostinger أو أي استضافة مشابهة

### بوابة الدفع
- حساب PayMob
- Integration ID للبطاقات
- Integration ID لـ Apple Pay

## إعداد الموقع

### 1. رفع الملفات

```bash
# باستخدام FTP
ftp -u ftp://user@hassanide.com public_html/

# أو باستخدام Git
git clone https://github.com/hfa0555916989/hassanide.git
cd hassanide/public_html
```

### 2. إعداد قاعدة البيانات

1. أنشئ قاعدة بيانات من cPanel
2. نفّذ ملف `database.sql`:
```sql
mysql -u username -p database_name < database.sql
```

3. نفّذ التحديثات:
```sql
mysql -u username -p database_name < database-update.sql
```

### 3. تكوين الإعدادات

حرر ملف `api/config.php`:

```php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');

// إعدادات PayMob
define('PAYMOB_API_KEY', 'your_api_key');
define('PAYMOB_SECRET_KEY', 'your_secret_key');
define('PAYMOB_INTEGRATION_CARD', 'your_integration_id');

// إعدادات الأمان
define('LICENSE_SECRET', 'random_32_char_string_here');
```

### 4. إعداد PayMob

1. سجل في [PayMob](https://paymob.com)
2. أنشئ Integration جديد للبطاقات
3. أضف Webhook URL:
   ```
   https://hassanide.com/api/webhook.php
   ```
4. انسخ Integration IDs للـ config

### 5. إعداد SSL

تأكد من تفعيل SSL:
```bash
# على Hostinger، SSL يُفعّل تلقائياً
# تأكد من إعادة التوجيه لـ HTTPS في .htaccess
```

## إعداد CI/CD

### GitHub Secrets

أضف هذه الـ Secrets في GitHub Repository:

| Secret | الوصف |
|--------|-------|
| `FTP_HOST` | عنوان FTP (ftp.hassanide.com) |
| `FTP_USER` | اسم مستخدم FTP |
| `FTP_PASSWORD` | كلمة مرور FTP |
| `WEBSITE_API_URL` | https://hassanide.com |
| `WEBSITE_API_KEY` | مفتاح API للتحديث التلقائي |

### إعداد Auto-Deploy

1. اذهب لـ Settings → Secrets and variables → Actions
2. أضف الـ Secrets المطلوبة
3. الآن كل push لمجلد `public_html` سينشر تلقائياً

## بناء HassanIDE

### متطلبات البناء

```bash
# Node.js 20+
node --version  # v20.x.x

# npm dependencies
npm install

# Python (للبناء)
python --version  # 3.x
```

### بناء محلي

```bash
# Windows
npm run gulp vscode-win32-x64
npm run gulp vscode-win32-x64-system-setup

# macOS
npm run gulp vscode-darwin-x64
npm run gulp vscode-darwin-x64-build-dmg

# Linux
npm run gulp vscode-linux-x64
npm run gulp vscode-linux-x64-build-deb
```

### إصدار نسخة جديدة

1. حدّث الإصدار في `package.json`
2. أنشئ tag جديد:
```bash
git tag v1.0.0
git push origin v1.0.0
```
3. سيبدأ GitHub Actions بالبناء تلقائياً

## التحقق من النشر

### اختبار الموقع

```bash
# تحقق من الصفحة الرئيسية
curl -I https://hassanide.com

# تحقق من API
curl https://hassanide.com/api/license-v2.php

# اختبار التسجيل
curl -X POST https://hassanide.com/api/auth.php \
  -d '{"action":"register","email":"test@test.com",...}'
```

### اختبار الترخيص

```bash
curl -X POST https://hassanide.com/api/license-v2.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "validate",
    "license_key": "PRO-TEST-1234-5678-ABCD",
    "machine_id": "test-machine"
  }'
```

### اختبار PayMob Webhook

```bash
# محاكاة webhook (للاختبار فقط)
curl -X POST https://hassanide.com/api/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"type":"TRANSACTION","obj":{"success":true,...}}'
```

## المراقبة

### سجلات الأخطاء

```bash
# مشاهدة سجلات PHP
tail -f /home/user/logs/error.log

# سجلات التراخيص
cat public_html/logs/license.log
```

### مراقبة الأداء

- استخدم Hostinger Analytics
- أو أضف Google Analytics
- راقب PayMob Dashboard للمدفوعات

## النسخ الاحتياطي

### قاعدة البيانات

```bash
# نسخة يومية
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql

# استعادة
mysql -u user -p database < backup_20250103.sql
```

### الملفات

```bash
# نسخ مجلد public_html
tar -czvf backup_$(date +%Y%m%d).tar.gz public_html/
```

## استكشاف الأخطاء

### خطأ 500

1. تحقق من سجل الأخطاء
2. تأكد من صلاحيات الملفات (644 للملفات، 755 للمجلدات)
3. تأكد من إعدادات قاعدة البيانات

### PayMob لا يعمل

1. تأكد من API Key
2. تأكد من Integration ID
3. تحقق من Webhook URL

### الترخيص لا يتحقق

1. تأكد من اتصال قاعدة البيانات
2. تحقق من LICENSE_SECRET
3. راجع سجل الأخطاء

## التواصل

للمساعدة التقنية:
- Email: dev@hassanide.com
- GitHub Issues: [github.com/hfa0555916989/hassanide/issues](https://github.com/hfa0555916989/hassanide/issues)

# نظام الترخيص - HassanIDE

## نظرة عامة

يوفر نظام الترخيص في HassanIDE طريقة آمنة لإدارة اشتراكات المستخدمين والوصول للميزات المدفوعة.

## الهيكل العام

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   HassanIDE     │────▶│   License API    │────▶│    Database     │
│   (Electron)    │     │   (PHP/MySQL)    │     │    (MySQL)      │
└─────────────────┘     └──────────────────┘     └─────────────────┘
         │                       │
         │                       ▼
         │              ┌──────────────────┐
         │              │     PayMob       │
         │              │   (Payments)     │
         │              └──────────────────┘
         │
         ▼
┌─────────────────┐
│  Local Storage  │
│  (Offline Mode) │
└─────────────────┘
```

## الباقات المتاحة

| الباقة | السعر | الأجهزة | الميزات |
|--------|-------|---------|---------|
| **Starter** | مجاني | 1 | المحرر الأساسي، Terminal، Git |
| **Pro** | 29 ر.س/شهر | 3 | AI Assistant، Templates، Cloud Sync |
| **Teams** | 99 ر.س/شهر | 10 | تعاون الفريق، SSO، دعم أولوية |

## سير العمل

### 1. شراء الاشتراك

```
المستخدم → الموقع → PayMob → Webhook → إنشاء License → إرسال Email
```

1. المستخدم يختار باقة من `/pricing.php`
2. يتم توجيهه لـ PayMob للدفع
3. عند نجاح الدفع، يُستقبل Webhook
4. يتم إنشاء License Key تلقائياً
5. يُرسل Email للمستخدم بالمفتاح

### 2. تفعيل الترخيص في HassanIDE

```
1. افتح HassanIDE
2. اضغط Ctrl+Shift+P
3. اكتب "Activate License"
4. الصق مفتاح الترخيص (PRO-XXXX-XXXX-XXXX-XXXX)
5. تم التفعيل!
```

### 3. التحقق الدوري

```
HassanIDE ─────[كل 24 ساعة]────▶ License API
                                      │
                                      ▼
                               التحقق من:
                               ✓ صلاحية المفتاح
                               ✓ تاريخ الانتهاء
                               ✓ عدد الأجهزة
                               ✓ حالة الاشتراك
```

### 4. وضع Offline

- يعمل HassanIDE بدون إنترنت لمدة **7 أيام**
- بعد 7 أيام بدون تحقق، يعود للباقة المجانية
- عند الاتصال، يتم التحقق تلقائياً

## API Endpoints

### التحقق من الترخيص

```http
POST /api/license-v2.php
Content-Type: application/json

{
    "action": "validate",
    "license_key": "PRO-XXXX-XXXX-XXXX-XXXX",
    "machine_id": "unique-machine-id",
    "machine_name": "My Computer"
}
```

**الاستجابة (نجاح):**
```json
{
    "valid": true,
    "plan": "pro",
    "plan_name": "Pro (احترافي)",
    "features": ["ai_assistant", "templates", "cloud_sync", ...],
    "expires_at": "2025-12-31T23:59:59",
    "days_remaining": 365,
    "max_devices": 3,
    "active_devices": 1,
    "devices": [
        {
            "machine_id": "xxx",
            "machine_name": "My Computer",
            "added_at": "2025-01-01",
            "last_seen": "2025-01-03"
        }
    ],
    "user": {
        "email": "user@example.com",
        "name": "Ahmed"
    },
    "offline_grace_days": 7
}
```

**الاستجابة (فشل):**
```json
{
    "valid": false,
    "error": "LICENSE_NOT_FOUND",
    "message": "License key not found"
}
```

### إزالة جهاز

```http
POST /api/license-v2.php
Content-Type: application/json

{
    "action": "remove_device",
    "license_key": "PRO-XXXX-XXXX-XXXX-XXXX",
    "machine_id": "device-to-remove"
}
```

### رموز الأخطاء

| الرمز | الوصف |
|-------|-------|
| `LICENSE_NOT_FOUND` | المفتاح غير موجود |
| `LICENSE_INACTIVE` | الترخيص معطل |
| `SUBSCRIPTION_EXPIRED` | الاشتراك منتهي |
| `MAX_DEVICES_REACHED` | تجاوز الحد الأقصى للأجهزة |
| `MACHINE_MISMATCH` | الجهاز غير مسجل |

## الميزات المقفلة

### كيفية قفل ميزة

```typescript
import { LicenseFeature } from 'vs/platform/license/common/license';
import { ILicenseService } from 'vs/platform/license/common/license';

class MyFeature {
    constructor(
        @ILicenseService private readonly licenseService: ILicenseService
    ) {}

    async useAdvancedFeature() {
        // التحقق من الميزة
        if (!this.licenseService.hasFeature(LicenseFeature.AIAssistant)) {
            // عرض dialog للترقية
            await showUpgradeDialog(LicenseFeature.AIAssistant, ...);
            return;
        }

        // تنفيذ الميزة
        this.doAdvancedStuff();
    }
}
```

### قائمة الميزات

| Feature | الباقة المطلوبة |
|---------|----------------|
| `basic_editor` | Starter |
| `terminal` | Starter |
| `git_basic` | Starter |
| `ai_assistant` | Pro |
| `templates` | Pro |
| `cloud_sync` | Pro |
| `hassan_panel` | Pro |
| `team_collaboration` | Teams |
| `sso_integration` | Teams |

## الأمان

### تخزين المفاتيح

- المفاتيح تُخزن محلياً بشكل مشفر
- يتم التحقق من Machine ID لمنع المشاركة
- Token مبني على JWT مع توقيع HMAC-SHA256

### حماية الAPI

- Rate limiting لمنع الـ brute force
- CORS مُعد لقبول الطلبات من HassanIDE فقط
- التحقق من صحة الـ input

## استكشاف الأخطاء

### "License key not found"

1. تأكد من نسخ المفتاح كاملاً
2. تحقق من عدم وجود مسافات زائدة
3. تأكد من صيغة المفتاح: `PRO-XXXX-XXXX-XXXX-XXXX`

### "Maximum devices reached"

1. افتح [hassanide.com/licenses](https://hassanide.com/licenses)
2. أزل أحد الأجهزة القديمة
3. أعد تفعيل الترخيص

### "Subscription expired"

1. جدد اشتراكك من [hassanide.com/pricing](https://hassanide.com/pricing)
2. سيتم تفعيل الترخيص الحالي تلقائياً

### مشاكل الاتصال

- تأكد من اتصالك بالإنترنت
- جرب تعطيل VPN/Proxy
- انتظر 24 ساعة للتحقق التلقائي

## للمطورين

### إضافة ميزة جديدة مقفلة

1. أضف الميزة في `LicenseFeature` enum:
```typescript
// src/vs/platform/license/common/license.ts
export const enum LicenseFeature {
    // ...
    NewFeature = 'new_feature'
}
```

2. حدد الباقة المطلوبة:
```typescript
export const FEATURE_PLAN_MAP: Record<LicenseFeature, LicensePlan> = {
    // ...
    [LicenseFeature.NewFeature]: LicensePlan.Pro
};
```

3. استخدم `hasFeature()` في الكود:
```typescript
if (licenseService.hasFeature(LicenseFeature.NewFeature)) {
    // الميزة متاحة
}
```

4. أضف الميزة في API (PHP):
```php
// api/license-v2.php - في getPlanFeatures()
'pro' => [
    // ...
    'new_feature'
]
```

### اختبار نظام الترخيص

```bash
# اختبار التحقق من ترخيص
curl -X POST https://hassanide.com/api/license-v2.php \
  -H "Content-Type: application/json" \
  -d '{"action":"validate","license_key":"TEST-1234-5678-ABCD-EFGH"}'
```

## الدعم

- 📧 Email: support@hassanide.com
- 💬 Discord: [discord.gg/hassanide](https://discord.gg/hassanide)
- 📚 Docs: [docs.hassanide.com](https://docs.hassanide.com)

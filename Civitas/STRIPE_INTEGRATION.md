# Stripe Integration — Civitas

حلّ هذا التكامل محل PayPal بالكامل. يُستخدَم Stripe لمعالجة الدفعات لمرة واحدة (one-time payment) لطلبات الخدمات.

> **ملاحظة:** تمت إزالة PayPal بالكامل من المشروع بتاريخ **2026-08-27**. تم حذف كل المراجع (Controller methods، Routes، JS، SDK script، متغيرات البيئة، وأعمدة قاعدة البيانات `PayPalOrderID` / `PayPalPayerID`). المكتبة المستخدمة الآن فقط هي `stripe/stripe-php`.

---

## 1. إعداد `.env`

أضف المتغيرات التالية إلى ملف `.env` (واتركها فارغة في `.env.example`):

```ini
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

القيم من لوحة تحكم Stripe:

| المتغير | المصدر |
| --- | --- |
| `STRIPE_KEY` | Stripe Dashboard → Developers → API keys → **Publishable key** (يبدأ بـ `pk_...`) |
| `STRIPE_SECRET` | Stripe Dashboard → Developers → API keys → **Secret key** (يبدأ بـ `sk_...`) |
| `STRIPE_WEBHOOK_SECRET` | Stripe Dashboard → Developers → Webhooks → **Signing secret** (يبدأ بـ `whsec_...`) |

أعد تحميل الإعدادات بعد التعديل:

```bash
php artisan config:clear
```

---

## 2. الاختبار المحلي (Test Mode)

### 2.1 تأكد أن الكود والمكتبة مثبّتان

```bash
composer install
```

### 2.2 تشغيل خادم Stripe CLI للـ webhook

من Stripe CLI، وجّه أحداث الويب هوك إلى الخادم المحلي:

```bash
stripe listen --forward-to http://localhost:8000/api/stripe/webhook
```

ستُطبع قيمة `whsec_...` مؤقتة، ضعها في `STRIPE_WEBHOOK_SECRET` ثم `php artisan config:clear`.

تأكد أن الخادم يعمل:

```bash
php artisan serve
```

### 2.3 تشغيل عامل الـ queue

الـ webhook يفوّض العمل الثقيل (تحديث حالة الطلب، سجل التدقيق، تطهير الكاش) إلى Queue Job (`FinalizeSuccessfulPayment`)، لذا شغّل عامل الـ queue:

```bash
php artisan queue:work --tries=3
```

### 2.4 بطاقات اختبار Stripe

| السيناريو | البطاقة |
| --- | --- |
| نجاح الدفع | `4242 4242 4242 4242` (أي تاريخ مستقبلي، CVC عشوائي) |
| رفض الدفع | `4000 0000 0000 0002` |

كما يمكن استخدام أرقام أخرى من [توثيق Stripe](https://docs.stripe.com/testing).

### 2.5 اختبار التدفق الكامل

1. سجّل الدخول كمدير، وانتقل إلى **Service Application** واختر مواطنًا.
2. اختر نوع الخدمة، حمّل المستندات المطلوبة، ثم اضغط **Proceed to Payment**.
3. سيظهر المودال مع محرر الدفع (Payment Element) التابع لـ Stripe — أدخل بطاقة الاختبار `4242 ...`.
4. اضغط **Pay** — عند النجاح تظهر شاشة الإيصال.
5. تحقق أن:
   - تم إنشاء سطر في جدول `Payments` بحالة `succeeded` مع `PaidAt`.
   - تم تحديث حالة طلب الخدمة إلى `Completed`.
   - تم إنشاء سطر في `audit_logs` (عبر الـ queue job).
   - سجل webhook event في جدول `Stripe_Webhook_Events`.

**ملاحظة حول العملية:** عند الضغط على الزر يُنشأ طلب الخدمة أولًا (نقطة `store`)، ثم يُنشأ PaymentIntent من السيرفر (نقطة `create-intent`) ويُحفظ سجل `Payment` بحالة `pending`، ثم يتم تأكيد الدفع في المودال، ويُختم الطلب عند وصول حدث `payment_intent.succeeded` عبر الـ webhook. **المبلغ يُحسب دائمًا من السيرفر** (من رسوم نوع الخدمة) ولا يُؤخذ من الواجهة.

---

## 3. الاختبارات الآلية (Feature Tests)

الاختبارات في `tests/Feature/StripePaymentTest.php` تغطي:

- إنشاء PaymentIntent بنجاح مع بيانات صحيحة يُرجع `client_secret` وينشئ سجل `Payment` بحالة `pending`.
- رفض إنشاء PaymentIntent لمستخدم غير مصرح به (لا يملك طلب الخدمة) → `403`.
- رفض إنشاء PaymentIntent ببيانات غير صحيحة → `422` / `404`.
- رفض webhook بتوقيع غير صالح → `400`.
- webhook صالح لحدث `payment_intent.succeeded` يحدّث `Payment` إلى `succeeded` و`PaidAt` ويُكمل طلب الخدمة.
- webhook لحدث `payment_intent.payment_failed` يحدّث `Payment` إلى `failed` ويحفظ سبب الفشل.
- webhook بنفس `event_id` مرسَل مرتين لا يُعالَج إلا مرة واحدة (idempotency).

### 3.1 قاعدة بيانات الاختبار (MySQL)

نظرًا لأن سلسلة الـ migrations الحالية مصمّمة لـ MySQL (تستخدم fulltext/ngram وخصائص غير مدعومة كفايةً في SQLite)، فإن الاختبارات تعمل ضد قاعدة MySQL محلية وليست SQLite.

أنشئ قاعدة بيانات MySQL محلية باسم `civitas_test` (بدون أي حاويات):

```bash
mysql -u root -p
```

ثم داخل جلسة MySQL:

```sql
CREATE DATABASE civitas_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON civitas_test.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

`phpunit.xml` مُهيّأ مسبقًا للاتصال بـ:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civitas_test
DB_USERNAME=root
DB_PASSWORD=root
```

عدّل القيم أعلاه إذا كانت إعدادات MySQL المحلية لديك مختلفة.

> **الحالة الحالية للاختبارات:** كتبتُ الاختبارات كاملةً وجاهزة للتشغيل، لكنها **لم تُنفَّذ فعليًا في هذه البيئة** لعدم توفر MySQL محلي في وقت الكتابة. شغّلها يدويًا بعد تجهيز قاعدة `civitas_test` بالأمر:

```bash
php artisan test --filter=StripePaymentTest
```

أو لتشغيل كل الاختبارات:

```bash
php artisan test
```

---

## 4. الانتقال من Test Mode إلى Live Mode

لا يتطلب أي تغيير في المنطق/الكود — فقط استبدال القيم في `.env`:

1. من **Stripe Dashboard** بدّل المفتاح إلى **Live mode**.
2. حدّث `.env`:
   ```ini
   STRIPE_KEY=pk_live_...
   STRIPE_SECRET=sk_live_...
   STRIPE_WEBHOOK_SECRET=whsec_...  # من webhook الخاص بالـ Live
   ```
3. أعد `php artisan config:clear`.
4. أنشئ نقطة webhook في **Live** على المسار `https://<domin>.com/api/stripe/webhook` مع **نفسك** الأحداث (`payment_intent.succeeded` و`payment_intent.payment_failed`)، وضع `STRIPE_WEBHOOK_SECRET` الجديدة من الطرف Live.
5. تأكد أن الخادم يستخدم HTTPS (يُفرض تلقائيًا في بيئة `production` عبر `AppServiceProvider`).
6. لا تُظهر `STRIPE_SECRET` أو `STRIPE_WEBHOOK_SECRET` في الواجهة أو الردود — لا يُرجَع من أي endpoint إلا `client_secret` (صالح للعميل فقط).

لا يوجد أي تغيير برمجي مطلوب بين الوضعين.

---

## 5. نقاط النهاية (Routes)

| الطريقة | المسار | الاسم | الحماية |
| --- | --- | --- | --- |
| `POST` | `/admin/service/payments/create-intent` | `admin.service.payments.create-intent` | `auth` + يتحقق أن المستخدم يملك طلب الخدمة |
| `POST` | `/api/stripe/webhook` | — | عام، مستثنى من CSRF صراحةً |

---

## 6. هيكل قاعدة البيانات

جدول `Payments` (تمت ترقيته وإنقاص أعمدة PayPal):

| العمود | النوع | الوصف |
| --- | --- | --- |
| `PaymentID` | uuid (PK) | مفتاح أساسي |
| `RequestID` | uuid (FK) | الربط بطلب الخدمة |
| `Amount` | decimal(10,2) | المبلغ |
| `Currency` | string | العملة (افتراضي `USD`) |
| `PaymentDate` | timestamp | تاريخ الإنشاء |
| `ReceiptNumber` | string nullable | رقم الإيصال |
| `StripePaymentIntentID` | string unique nullable | معرّف PaymentIntent من Stripe |
| `Status` | string | `pending` / `succeeded` / `failed` / `refunded` |
| `Metadata` | json nullable | بيانات إضافية |
| `PaidAt` | timestamp nullable | وقت اكتمال الدفع |
| `FailureReason` | string nullable | سبب الفشل من Stripe |

جدول `Stripe_Webhook_Events` (لضمان **idempotency**):

| العمود | النوع | الوصف |
| --- | --- | --- |
| `id` | bigint (PK) | — |
| `EventID` | string unique | معرّف حدث Stripe (`evt_...`) |
| `EventType` | string | نوع الحدث |
| `created_at` | timestamp | — |

---

## 7. ملفات الكود الرئيسية

- `app/Http/Controllers/StripePaymentController.php` — إنشاء PaymentIntent + معالجة الـ webhook.
- `app/Jobs/FinalizeSuccessfulPayment.php` — العمل الثقيل بعد نجاح الدفع (queue job).
- `app/Models/Payment.php` — نموذج الدفع.
- `app/Models/StripeWebhookEvent.php` — نموذج أحداث الـ webhook.
- `resources/views/admin/service-application.blade.php` — واجهة الدفع و`showPaymentModal()`.
- `resources/views/layouts/admin.blade.php` — تضمين `https://js.stripe.com/v3/`.
- `config/services.php` — إعدادات Stripe.
- `tests/Feature/StripePaymentTest.php` — الاختبارات الآلية.

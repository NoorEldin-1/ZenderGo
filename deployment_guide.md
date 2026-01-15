# خطة نشر مشروع Zender على Hostinger VPS (CyberPanel)

هذه خطة تفصيلية لنشر مشروع Laravel + Node.js (Zender) على سيرفر Hostinger VPS يعمل بنظام تشغيل AlmaLinux/Ubuntu مع لوحة تحكم CyberPanel.

## المتطلبات المسبقة (Prerequisites)

1.  **Doman Name**: نطاق (Domain) محجوز ومرتبط بـ Cloudflare (مستحسن) أو مبارشرة بـ IP السيرفر.
2.  **VPS**: الخطة التي اخترتها (KVM 2) ممتازة (8GB RAM, 2 vCPU).
3.  **CyberPanel**: تم تنصيبه عند الشراء (اختيار CyberPanel on AlmaLinux 9).

---

## الخطوة 1: الإعداد الأولي للسيرفر (Server Initial Setup)

1.  **الدخول للسيرفر (SSH):**

    - استخدم Terminal في جهازك أو Putty.
    - الأمر: `ssh root@YOUR_SERVER_IP`
    - أدخل كلمة المرور الخاصة بالـ Root.

2.  **تحديث النظام:**

    ```bash
    dnf update -y  # لنظام AlmaLinux
    ```

3.  **تنصيب Redis (ضروري جداً للمشروع):**
    مشروعك يعتمد على Redis بشكل كلي (Queues, Caching, Throttling).

    ```bash
    dnf install redis -y
    systemctl enable redis
    systemctl start redis
    # تأكد أنه يعمل
    redis-cli ping # يجب أن يرد بـ PONG
    ```

4.  **تنصيب Node.js (أحدث إصدار مستقر):**
    CyberPanel يأتي مع نسخ متعددة، لكن يفضل تنصيب NVM للتحكم الكامل.
    ```bash
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
    source ~/.bashrc
    nvm install 20
    nvm use 20
    node -v # تأكد أنه v20.x.x
    npm install -g pm2 # لتشغيل الـ Whatsapp Server في الخلفية
    ```

---

## الخطوة 2: إعداد الدومين والموقع (CyberPanel Setup)

1.  **الدخول للوحة CyberPanel:**

    - الرابط عادة: `https://YOUR_SERVER_IP:8090`
    - أدخل بيانات الدخول (admin / password).

2.  **إنشاء موقع جديد (للتطبيق الرئيسي Laravel):**

    - اذهب إلى **Websites** -> **Create Website**.
    - **Package**: Default
    - **Owner**: Admin
    - **Domain Name**: `yourdomain.com` (بدون www)
    - **Email**: ايميلك
    - **PHP**: اختر PHP 8.2 (أو 8.3 إذا كان مدعوماً في كودك، [composer.json](file:///c:/work/projects/zender/composer.json) يطلب `^8.2`).
    - **SSL**: فعل خيار SSL.
    - اضغط **Create Website**.

3.  **إنشاء دومين فرعي (للـ Node.js Server):**
    سنحتاج دومين فرعي لربط الـ Whatsapp Server بشكل آمن (WSS/HTTPS) ولتجنب مشاكل المنافذ.
    - **Websites** -> **Create Website** (مرة أخرى) أو **Child Domains**.
    - **Domain Name**: `wa.yourdomain.com`
    - **PHP**: أي نسخة (لن نستخدم PHP هنا).
    - **SSL**: فعل خيار SSL.
    - اضغط **Create Website**.

---

## الخطوة 3: رفع ملفات تطبيق Laravel

1.  **رفع الملفات:**

    - يمكنك استخدام **FTP** (FileZilla) للاتصال بالسيرفر.
    - المسار: `/home/yourdomain.com/public_html`
    - احذف ملف `index.html` الافتراضي.
    - ارفع ملفات مشروعك بالكامل (ما عدا `node_modules` و `vendor`).
    - **أو الأفضل:** استخدام Git إذا كان الكود على GitHub.

    ```bash
    cd /home/yourdomain.com/public_html
    rm -rf *
    git clone https://github.com/NoorEldin-1/zender.git .
    ```

2.  **ضبط الصلاحيات (Permissions):**

    ```bash
    chown -R yourdomain.com:yourdomain.com /home/yourdomain.com/public_html
    chmod -R 775 storage bootstrap/cache
    ```

    _ملاحظة: استبدل `yourdomain.com` باسم المستخدم الخاص بالموقع في CyberPanel، عادة يكون نفس اسم الدومين._

3.  **تنصيب الاعتماديات (Dependencies):**

    ```bash
    cd /home/yourdomain.com/public_html
    # تنصيب حزم PHP
    /usr/local/lsws/lsphp82/bin/php /usr/bin/composer install --optimize-autoloader --no-dev

    # تنصيب حزم الواجهة الأمامية وبناؤها
    npm install
    npm run build
    ```

4.  **إعداد ملف .env:**

    ```bash
    cp .env.example .env
    nano .env
    ```

    - عدّل بيانات قاعدة البيانات (التي سننشئها تالياً).
    - عدّل `APP_URL=https://yourdomain.com`
    - عدّل `REDIS_HOST=127.0.0.1`

5.  **إنشاء قاعدة البيانات:**

    - من CyberPanel -> **Databases** -> **Create Database**.
    - اختر الموقع `yourdomain.com`.
    - اسم القاعدة، اسم المستخدم، وكلمة المرور.
    - احفظ هذه البيانات وضعها في ملف `.env`.

6.  **تجهيز التطبيق:**

    ```bash
    # توليد المفتاح
    /usr/local/lsws/lsphp82/bin/php artisan key:generate

    # عمل الـ Migrations
    /usr/local/lsws/lsphp82/bin/php artisan migrate --force

    # ربط التخزين
    /usr/local/lsws/lsphp82/bin/php artisan storage:link
    ```

7.  **توجيه الـ Pubic Folder:**
    في CyberPanel، الـ Root هو `public_html`. لكن Laravel يحتاج `public_html/public`.
    - اذهب لـ CyberPanel -> **Websites** -> **List Websites** -> **Manage** -> **vHost Conf**.
    - ابحث عن `docRoot` وغيره إلى: `$VH_ROOT/public_html/public`
    - اضغط **Save** وسيعيد تشغيل الـ LiteSpeed.

---

## الخطوة 4: تشغيل Whatsapp Server (Node.js)

1.  **تجهيز الملفات:**

    - المجلد موجود داخل مشروعك في `whatsapp-server`.
    - ادخل للمجلد:

    ```bash
    cd /home/yourdomain.com/public_html/whatsapp-server
    ```

2.  **الإعداد:**

    ```bash
    # تنصيب المكاتب
    npm install

    # بناء ملفات TypeScript
    npm run build

    # ملف الإعدادات
    cp .env.example .env # إن وجد، أو تأكد من إعدادات src/config.ts
    ```

3.  **التشغيل باستخدام PM2:**
    ```bash
    pm2 start dist/server.js --name "zender-wa"
    pm2 save
    pm2 startup
    ```

---

## الخطوة 5: ربط Whatsapp Server بالدومين الفرعي (Reverse Proxy)

نريد أن يتمكن التطبيق والمستخدمون من الاتصال بـ `wa.yourdomain.com` بدلاً من `IP:21465`.

1.  في CyberPanel، اذهب إلى الموقع الفرعي `wa.yourdomain.com`.
2.  اضغط **Manage**.
3.  ابحث عن **vHost Conf** أو **Rewrite Rules** (يختلف قليلاً حسب نسخة OpenLiteSpeed، لكن الأفضل عبر إعدادات القوالب أو vHost).
4.  في **OpenLiteSpeed** (المستخدم في CyberPanel)، الطريقة الأسهل لعمل Proxy هي عبر **Context**.

    - لكن الطريقة الأسرع في CyberPanel هي عبر تعديل **vHost Conf** مباشرة لإضافة إعدادات الـ Proxy.
    - أضف التالي في نهاية الملف (داخل تاج `<VirtualHost>`):

    ```apache
    <IfModule mod_proxy.c>
        ProxyRequests Off
        ProxyPreserveHost On
        # لدعم WebSocket
        RewriteEngine On
        RewriteCond %{HTTP:Upgrade} =websocket [NC]
        RewriteRule /(.*)           ws://127.0.0.1:21465/$1 [P,L]

        # للاتصالات العادية
        ProxyPass / http://127.0.0.1:21465/
        ProxyPassReverse / http://127.0.0.1:21465/
    </IfModule>
    ```

    _(تأكد أن المنفذ 21465 هو المستخدم في كود Node.js)_

    - اضغط **Save** وأعد تشغيل LiteSpeed.

---

## الخطوة 6: إعداد الـ Queues و Scheduler (System Workers)

1.  **Laravel Scheduler (Cron Job):**

    - من CyberPanel -> **Websites** -> **List Websites** -> **Manage** -> **Cron Jobs**.
    - أضف Cron جديد:
    - الوقت: `* * * * *` (كل دقيقة)
    - الأمر: `/usr/local/lsws/lsphp82/bin/php /home/yourdomain.com/public_html/artisan schedule:run >> /dev/null 2>&1`

2.  **Laravel Queue (Supervisor):**
    - CyberPanel لديه مدير عمليات (Process Manager) لكن الأفضل استخدام Supervisor المباشر عبر SSH لضمان الاستقرار.
    - تنصيب Supervisor:
    ```bash
    dnf install supervisor -y
    systemctl enable supervisord
    systemctl start supervisord
    ```
    - إنشاء ملف التكوين:
    ```bash
    nano /etc/supervisord.d/zender-worker.ini
    ```
    - المحتوى:
    ```ini
    [program:zender-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=/usr/local/lsws/lsphp82/bin/php /home/yourdomain.com/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=yourdomain.com  ; اسم المستخدم الخاص بالموقع
    numprocs=2           ; عدد العمليات المتوازية
    redirect_stderr=true
    stdout_logfile=/home/yourdomain.com/public_html/storage/logs/worker.log
    stopwaitsecs=3600
    ```
    - تفعيل الوركر:
    ```bash
    supervisorctl reread
    supervisorctl update
    supervisorctl start zender-worker:*
    ```

---

## الخطوة 7: التحقق النهائي

1.  افتح `https://yourdomain.com`.
2.  سجل حساب جديد.
3.  في صفحة ربط الواتساب، تأكد أن QR Code يظهر.
    - هذا يعني أن `wa.yourdomain.com` يعمل والاتصال آمن.
4.  جرب إرسال حملة صغيرة وتأكد أنها خرجت من الحالة Pending إلى Processing -> Sent.

---

## الخطوة 8: مراقبة الأداء (Monitoring)

كيف تعرف أن السيرفر "مضغوط" أو مرتاح؟ لديك 3 أدوات أساسية:

### 1 لوحة CyberPanel (نظرة عامة)
- ادخل للوحة التحكم.
- من القائمة الجانبية اختر **Server Status** -> **Top Processes**.
- ستشاهد رسوم بيانية لاستهلاك الـ CPU والـ RAM بشكل عام.
- **الميزة:** سهلة وسريعة.
- **العيب:** لا تتحدث لحظة بلحظة (تتأخر بضع ثواني).

### 2 مراقبة سيرفر الواتساب (PM2 Monitor)
هذه أهم أداة لمراقبة خدمة الواتساب تحديداً.
- ادخل عبر SSH.
- اكتب الأمر:
  ```bash
  pm2 monit
  ```
- ستفتح شاشة تفاعلية مقسمة:
  - **Global Logs:** تظهر لك الأخطاء والعمليات لحظة حدوثها.
  - **Process List:** تظهر لك الذاكرة (Memory) والمعالج (CPU) الذي يستهلكه الـ whatsapp-server، ولو عندك 30 مستخدم سترى الاستهلاك يرتفع وينخفض هنا بوضوح.

### 3 مراقبة السيرفر بالكامل (HTOP)
الأداة الاحترافية الأولى لمديري السيرفرات.
- من SSH اكتب:
  ```bash
  htop
  ```
- **الشريط العلوي (Bars):** كل شريط يمثل نواة (Core) من المعالج. إذا كانت كلها حمراء (100%) فهناك ضغط.
- **Mem:** يوضح الذاكرة المستخدمة. تذكر أن Linux يستخدم الذاكرة الفارغة للـ Caching، لذا انظر للون الأخضر (الاستخدام الفعلي).
- **Load Average:** (تجدها بالأعلى يمين) 3 أرقام (مثلاً: `0.54  0.40  0.30`).
  - الرقم الأول: متوسط الضغط آخر دقيقة.
  - الرقم الثاني: آخر 5 دقائق.
  - الرقم الثالث: آخر 15 دقيقة.
  - **القاعدة:** إذا كان الرقم أقل من عدد الأنوية (2 في خطتك)، فالسيرفر "مرتاح" جداً. إذا تجاوز 2.0، هناك طابور انتظار للمعالج.

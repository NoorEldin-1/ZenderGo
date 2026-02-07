# Zender - SaaS WhatsApp Marketing Platform (Baileys Edition)

Zender is a high-performance, SaaS-ready WhatsApp marketing platform built with **Laravel 10**, **Node.js**, and **Baileys**. Unlike traditional solutions that rely on heavy browser automation (Puppeteer/WPPConnect), Zender uses the lightweight **Baileys** library to communicate directly with WhatsApp via WebSocket, reducing RAM usage by **90%**.

This edition is optimized for shared hosting/VPS environments, featuring an "On-Demand" session lifecycle, Redis-powered atomic locks, and a complete subscription management system (SaaS).

---

## 🚀 Key Features

### ⚡ Performance & Architecture

- **Baileys Integration:** Uses ~50MB RAM per session (vs 500MB+ with Puppeteer).
- **On-Demand Lifecycle:** Sessions "sleep" when idle and "wake up" instantly for campaigns.
- **Redis Atomic Locks:** Prevents race conditions and "Thundering Herd" issues during high-volume campaigns.
- **Smart Queue System:** Handles thousands of messages with rate limiting and retry logic.

### 💼 SaaS & Subscription System

- **Trial & Paid Plans:** Automatic trial upon registration.
- **Payment Gateways:** Built-in support for manual payments (Vodafone Cash, Instapay) with receipt upload.
- **Subscription Control:** Middleware (`subscription.active`) ensures expired users cannot access core features.
- **Admin Panel:** comprehensive dashboard for managing users, approving payments, and system settings.

### 📱 WhatsApp Features

- **Multi-Device Support:** Connect via **QR Code** or **Pairing Code** (Phone Number).
- **Campaign Builder:** Send Text, Images, and Collages.
- **Smart Contact Import:** Auto-detects columns (Name, Phone) from Excel/CSV.
- **Detailed Reports:** Track Sent, Failed, and Pending messages in real-time.

---

## 🛠️ Tech Stack

- **Backend:** Laravel 10 (PHP 8.2+)
- **WhatsApp Server:** Node.js (TypeScript) + Baileys
- **Database:** MySQL 8.0+
- **Queue & Cache:** Redis (Required)
- **Frontend:** Blade Templates + Bootstrap 5 + Vanilla JS

---

## 📦 Installation Guide

### Prerequisites

1.  **PHP 8.2+** with extensions (`pcntl`, `redis`, `gd`, `intl`).
2.  **Node.js 18+** & NPM.
3.  **Redis Server** (Must be running).
4.  **Composer**.

### 1. Clone & Setup Backend

```bash
git clone https://github.com/yourusername/zender.git
cd zender

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Copy Environment file
cp .env.example .env

# Generate Key
php artisan key:generate
```

### 2. Configure Environment (.env)

Edit `.env` and set your database and Redis credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zender
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 3. Setup Database & Assets

```bash
# Run Migrations
php artisan migrate --seed

# Link Storage
php artisan storage:link

# Install Frontend Assets
npm install
npm run build
```

### 4. Setup WhatsApp Server (Baileys)

The WhatsApp server is located in `whatsapp-server/`.

```bash
cd whatsapp-server

# Install Dependencies
npm install

# Create .env for Node.js
cp .env.example .env
# Ensure PORT and REDIS match your Laravel .env
```

To run the server in development:

```bash
npm run dev
```

To build for production:

```bash
npm run build
npm start
```

### 5. Start Queues (Crucial)

You **must** run the Laravel queue worker to process campaigns.

```bash
php artisan queue:work redis --tries=3 --timeout=90
```

---

## 🏛️ Architecture Deep Dive

### 1. The "On-Demand" Lifecycle (RAM Optimization)

Traditional WhatsApp bots keep the browser open 24/7, consuming massive RAM. Zender introduces an intelligent lifecycle:

1.  **Sleep:** After 5 minutes of inactivity, the Baileys socket closes. RAM usage drops to near zero.
2.  **Wake:** When a campaign starts, the `SendWhatsappCampaign` job triggers a "Wake Up" signal.
3.  **Check:** The system verifies connection health (`checkConnection`).
4.  **Send:** Messages are dispatched.
5.  **Shutdown:** After the queue is empty, the session returns to sleep.

This allows a 2GB VPS to handle **50+ concurrent users** instead of just 4-5.

### 2. Redis & "Thundering Herd" Protection

When a user sends a campaign to 1,000 contacts, 1,000 jobs are dispatched instantly. Without protection, all 1,000 jobs would try to "wake up" the session simultaneously, crashing the server.

Zender uses **Atomic Locks** (`Cache::lock`):

- **Job #1** acquires the lock and wakes the session.
- **Job #2-1000** checks the lock/status and waits.
- Once the session is active, the lock releases, and all jobs proceed to send messages using the arguably active connection.

---

## 💎 SaaS & Subscription Model

Zender is built as a multi-tenant SaaS. Here's how the subscription flow works:

### 1. Trial & Expiry

- Every new user gets a **Trial Period** (configurable in Admin Panel).
- When `Subscription::isExpired()` is true, the `subscription.active` middleware blocks access to:
    - Campaign Creation
    - Contact Import
    - WhatsApp Connection
- Users are redirected to the **Renewal Page**.

### 2. Renewal Flow (Offline Payments)

Since in many regions (like Egypt) automated payments (Stripe) aren't always preferred for B2B, Zender prioritizes **Manual/Offline Payments**:

1.  **User** visits "My Subscription".
2.  **User** sees the payment details (Vodafone Cash / Instapay Number).
3.  **User** transfers the amount and uploads a **Screenshot/Receipt**.
4.  **System** creates a `PaymentRequest` (Pending).
5.  **Admin** receives notification -> Verifies money -> Clicks "Approve".
6.  **System** automatically:
    - Extends the subscription (`ends_at + 30 days`).
    - Notifies the user.
    - Unlocks the account.

---

## 📱 WhatsApp Connection Methods

Zender supports the latest Baileys connection methods:

### Option A: QR Code (Standard)

1.  Go to **Connect WhatsApp**.
2.  A QR code generates in real-time.
3.  Scan with WhatsApp (Linked Devices).

### Option B: Pairing Code (phone Number)

1.  Select **"Use Pairing Code"**.
2.  Enter your phone number (e.g., `201xxxxxxxxx`).
3.  A generic 8-digit code appears on the screen (`ABCD-1234`).
4.  Enter this code on your phone's WhatsApp notification.

**Note:** If connection fails or hangs, use the **"Reset Session"** button. This triggers a `Clean Slate` protocol:

- Deletes local auth files.
- Flushes Redis session keys.
- Forces a fresh WebSocket handshake.

---

## 🛡️ Troubleshooting

### Message Pending?

- Ensure `php artisan queue:work` is running.
- Check Redis connection: `ping` in `redis-cli`.

### "Session Disconnected"?

- Click to standard Refresh.
- If persistent, click **Reset Session** (Garbage Can Icon) to clear stale auth states.

### Node.js Server Error?

- Check logs in `whatsapp-server/storage/logs/`.
- Ensure ports (3000/3001) aren't blocked by firewall.

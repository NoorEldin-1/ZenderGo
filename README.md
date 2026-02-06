# Zender - WhatsApp Marketing Automation Platform

Zender is a powerful, full-featured WhatsApp marketing automation platform designed to help users manage contacts, create engaging campaigns, and track their performance effectively. It combines a robust Laravel backend with a high-performance Node.js WhatsApp server to deliver a seamless messaging experience.

## 🚀 Project Overview

The core idea behind Zender is to provide a reliable and user-friendly interface for sending WhatsApp campaigns. Unlike simple bulk senders, Zender focuses on:

-   **Smart Automation:** Handles queues, rate limiting, and session management automatically.
-   **Performance:** Uses Redis for caching and background job processing to ensure the UI remains responsive.
-   **Safety:** Implements a quota system and "thundering herd" protection to prevent server overload and WhatsApp bans.
-   **User Experience:** Features a modern, responsive UI with dark/light mode support and intuitive flows.

---

## 🛠 Tech Stack & Tools

This project utilizes a modern and robust technology stack:

### Backend (Laravel ecosystem)

-   **Framework:** Laravel 12 (PHP 8.2+)
-   **Database:** MySQL (Persistent data storage)
-   **Caching & Queue:** Redis (Crucial for performance and background jobs)
-   **Dependencies:**
    -   `predis/predis`: For Redis interaction.
    -   `maatwebsite/excel`: For handling contact imports/exports.
    -   `laravel/framework`: Core framework.

### Frontend

-   **Templating:** Blade Templates
-   **Styling:** Tailwind CSS 4.0
-   **Build Tool:** Vite
-   **Interactivity:** Vanilla JS + AJAX for smooth, SPA-like experiences without the complexity of a full JS framework.

### WhatsApp Server (Microservice)

-   **Runtime:** Node.js
-   **Framework:** Express.js
-   **Core Library:** `@wppconnect-team/wppconnect` (For WhatsApp Web automation)
-   **WebSocket:** `socket.io` (For real-time updates)
-   **Database:** MongoDB (For WPPConnect token storage)
-   **Language:** TypeScript

---

## 📂 The `whatsapp-server` Folder

The `whatsapp-server` directory contains a standalone Node.js application that acts as the bridge between Laravel and WhatsApp.

-   **Role:** It runs a headless browser instance (Chrome/Chromium) to emulate WhatsApp Web.
-   **Communication:** Laravel communicates with this server via HTTP API (to send messages, check status) and receives webhooks/updates.
-   **Key Files:**
    -   `src/server.ts` & `src/index.ts`: The entry points that initialize the Express server and WPPConnect.
    -   `src/config.ts`: Configuration for ports, webhooks, and browser arguments.
-   **Performance:** It handles the heavy lifting of encryption and protocol communication with WhatsApp servers.

---

## 🏗️ Architecture Deep Dive

Zender is built with a sophisticated architecture designed to handle the complexity of WhatsApp automation at scale.

### 1. The "Aggressive" Session Lifecycle (RAM Optimization)

One of the biggest challenges with WhatsApp automation is the high RAM usage of headless browsers (Chrome/Chromium). Running 50 concurrent sessions would normally require massive servers (32GB+ RAM).
**Zender solves this with an intelligent "On-Demand" Lifecycle:**

-   **Idle = Dead:** Sessions are **NOT** kept alive permanently. If a user is not sending a campaign, their session is closed to free up resources.
-   **Wake-on-Demand:** When a campaign starts, the `SendWhatsappCampaign` job triggers a "Wake" sequence (`SessionManager::wakeSession`).
-   **Safety Checks:** Before waking a session, the system checks:
    -   **RAM Usage:** If server RAM > 80%, new sessions are rejected/queued.
    -   **Concurrency Limit:** Strict limit (e.g., 3 active sessions) to prevent crashing the VPS.
-   **Auto-Cleanup:** Once a batch finishes, the session is **immediately closed** (`cleanupAfterBatch`), returning RAM to the pool.

### 2. Quota System: Preventing Race Conditions (The "Lua" Solution)

Managing quotas (e.g., "100 messages per 5 hours") is trivial for one user but complex for concurrent campaigns.

-   **The Problem:** If a user opens 5 tabs and clicks "Send" simultaneously, a standard database check (`if current < limit`) would fail (Race Condition), allowing them to send 500 messages instead of 100.
-   **The Solution (Redis Atomic Lua Scripts):**
    -   Zender uses a custom **Lua script** running inside Redis to perform a "Check-and-Increment" operation in a single atomic step.
    -   **Fallback:** If Redis fails, the system automatically degrades to **Pessimistic DB Locking** (`lockForUpdate`), ensuring data integrity is never compromised.

### 3. "Thundering Herd" Protection

When a user launches a campaign to 1,000 contacts, we don't spam the queue instantly.

-   **Atomic Locks:** `Cache::lock("campaign_send_lock:{$userId}")` ensures a user cannot submit the "Send" form twice while the server is processing the request.
-   **Job Locking:** `Cache::lock("campaign_job_lock:{$userId}")` ensures that even if multiple queue workers are running, messages for a _single user_ are processed sequentially. This mimics human behavior and prevents WhatsApp from flagging the account for "bot-like speed".

---

## ⚡ Performance & Caching Internals

Zender is architected for high performance and scalability:

1.  **Redis Caching:**

    -   **Session Storage:** PHP sessions are stored in Redis for faster access.
    -   **Data Caching:** Frequently accessed data (like quota status, active campaigns) is cached to reduce database queries.
    -   **Atomic Locks:** Uses Redis atomic locks (`Cache::lock`) to prevent "double send" issues when multiple users try to send campaigns simultaneously.

2.  **Queue System:**

    -   **Asynchronous Sending:** Campaign messages are NOT sent immediately during the HTTP request. Instead, they are dispatched to a Redis queue (`SendWhatsappCampaign`).
    -   **Throttling:** The system automatically adds delays (e.g., 15s) between messages to mimic human behavior and avoid blocking.

3.  **Heavy Rate Limiting:**
    -   Middleware like `rate.heavy` protects resource-intensive routes (like Import, Bulk Delete) from abuse.

---

## 🌟 Pages & Features Breakdown

### 1. **Authentication (Login/Register)**

-   **Features:** Secure login, registration with email verification flow, and password reset.
-   **Purpose:** Ensures strict access control. User sessions are managed via Redis.
-   **Reconnect Flow:** Special flow to re-establish WhatsApp connection if the session becomes invalid.

### 2. **Dashboard**

-   **Features:** Provides a high-level overview of the account status.
-   **Purpose:** Quick access to key metrics and navigation.

### 3. **Contacts Management**

-   **Smart Import:** Upload CSV/Excel files. The system attempts to auto-map columns (Name, Phone) and allows manual remapping.
-   **Preview:** Shows a preview of valid/invalid contacts before finalizing insertion.
-   **Filters:** Filter by "Featured" (Star icon), Date Added, or "Last Contacted" date.
-   **Bulk Actions:** efficient bulk deletion of contacts.
-   **Visuals:** "3-dots" menu for quick actions on individual contacts.

### 4. **Campaigns**

-   **Create Campaign:**
    -   Select contacts (up to 10 per batch for safety).
    -   Write personalized messages (supports `{{ name }}` placeholders).
    -   Upload images (Auto-creates a collage if multiple images are selected).
-   **Quota System:** Displays real-time sending limits (e.g., 100 messages/5 hours). Prevents sending if quota is exceeded.
-   **Status Tracking:** Real-time feedback on campaign progress (Pending -> Sent).
-   **WhatsApp Status:** Checks if the phone is connected/Internet is active on the phone.

### 5. **Templates**

-   **Features:** Create, edit, and delete message templates.
-   **Sharing:** Unique feature to **Share Templates** with other users via a link or ID. The receiver can accept or reject the shared template.

### 6. **Subscription & Payment**

-   **Locked Content:** Users with inactive subscriptions are blocked from crucial features (like sending campaigns) via `subscription.active` middleware.
-   **Payment Flow:** Integrated payment gateway for renewing subscriptions.

### 7. **User Guide**

-   **Features:** A dedicated page explaining how to use the platform, use the installment calculator, and best practices.

### 8. **👮 Admin Panel**

The project includes a fully featured **Admin Panel** accessible at `/admin`. It uses a separate authentication guard (`admin` guard) for security.

-   **User Management:**
    -   View all registered users and their details.
    -   **Suspend/Unsuspend:** Limit access for abusive users.
    -   **Subscription Control:** Manually force-activate subscriptions if needed.
-   **Payment Requests (Offline):**
    -   Review bank transfer/offline payment proofs uploaded by users.
    -   **Approve/Reject:** Approving a request automatically activates the user's subscription. Rejection sends usage back to the user.
    -   **Audit Log:** Tracks which admin reviewed the request and adds notes.
-   **System Settings:**
    -   Configure global site settings directly from the UI.
-   **Dashboard:**
    -   View high-level statistics about the platform's growth and revenue.

---

## 💻 Local Installation Guide

To run this project locally, you need two terminals: one for Laravel and one for the WhatsApp Server.

### Prerequisites

-   [PHP 8.2+](https://www.php.net/downloads)
-   [Composer](https://getcomposer.org/)
-   [Node.js 18+](https://nodejs.org/)
-   [Redis](https://redis.io/) (Must be running locally)
-   [MySQL](https://www.mysql.com/)

### Step 1: Setup Laravel Backend

1.  Navigate to the project root:
    ```bash
    cd c:\work\projects\zender
    ```
2.  Install PHP dependencies:
    ```bash
    composer install
    ```
3.  Configure Environment:
    -   Copy `.env.example` to `.env`.
    -   Set database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    -   Set Redis credentials (`REDIS_HOST`, etc.).
4.  Generate Key & Migrate:
    ```bash
    php artisan key:generate
    php artisan migrate
    ```
5.  Start the Development Server:
    ```bash
    php artisan serve
    ```
6.  **Crucial:** Start the Queue Worker (in a separate terminal):
    ```bash
    php artisan queue:work
    ```
    _Without this, campaigns will stay "Pending" forever._
7.  Start the Scheduler (for periodic tasks):
    ```bash
    php artisan schedule:work
    ```

### Step 2: Setup WhatsApp Server

1.  Navigate to the server folder:
    ```bash
    cd whatsapp-server
    ```
2.  Install Node dependencies:
    ```bash
    npm install
    ```
3.  Start the Server:
    ```bash
    npm run start
    ```
    _This will compile TypeScript and start the server on port 21465 (default)._

### Step 3: Verify Connection

1.  Open your browser to `http://localhost:8000`.
2.  Login/Register.
3.  Go to Settings/Profile to scan the QR code and link your WhatsApp.
4.  Once linked, the Dashboard should show "Connected".

---

Enjoy building with Zender! 🚀

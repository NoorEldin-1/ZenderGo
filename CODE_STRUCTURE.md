# Zender Technical Documentation & Codebase Structure

This document provides a detailed, technical breakdown of the Zender project. It explains exactly what each key file does, its internal logic, and its relationship with other parts of the system.

---

## 🏗️ Core Architecture Concepts

Before diving into files, it is crucial to understand three core architectural patterns used in Zender:

1.  **Aggressive Session Lifecycle (`SessionManager`)**:

    -   To save RAM, WhatsApp sessions are **NOT** kept alive permanently.
    -   They are "woken up" only when sending a campaign and closed immediately after.
    -   _Benefit:_ Allows running 50+ concurrent users on a small VPS (4GB RAM) instead of requiring 32GB+.

2.  **Atomic Quota System (`CampaignQuotaService`)**:

    -   Uses Redis `INCRBY` and Lua scripts to blindly check-and-reserve quota in one atomic step.
    -   _Benefit:_ Prevents "Race Conditions" where a user with 5 tabs open could send 5x their limit.

3.  **Thundering Herd Protection**:
    -   Uses Redis Locks (`Cache::lock`) to prevent multiple jobs from processing the same user simultaneously.
    -   _Benefit:_ Protects the server from CPU spikes when a user blasts a 1000-contact campaign.

---

## 🔐 Module A: Authentication & Session Management

This module handles user access and the connection to the WhatsApp Server microservice.

### 1. `app/Http/Controllers/AuthController.php`

-   **Role:** The entry point for Login, Register, and WhatsApp connection/reconnection.
-   **Key Logic:**
    -   `login()`: Verified password. If user has a WhatsApp session, it logs them in. If not, it redirects to `reconnect`.
    -   `startReconnect()`: **Critical.** calls `WhatsAppService` to generate a NEW session name and QR code. It forces a fresh session to ensure stability.
-   **Relationships:**
    -   **Calls:** `WhatsAppService` (to talk to Node.js server), `SessionManager` (to manage lifecycle), `User` (model).
    -   **Why?** The controller orchestrates the UI flow, while the Service handles the complex API calls.

### 2. `app/Services/SessionManager.php`

-   **Role:** The "Brain" of the RAM optimization strategy.
-   **Key Logic:**
    -   `wakeSession(User $user)`: The most important method. It checks RAM usage. If good, it calls `WhatsAppService->startSession()`. It polls until "CONNECTED" state is reached.
    -   `closeSession(User $user)`: Called immediately after a campaign batch finishes.
    -   `forceCloseOldestSession()`: If RAM > 80%, it kills the least active session to make room for a new one.
-   **Relationships:**
    -   **Called By:** `SendWhatsappCampaign` (Job), `CampaignController` (pre-flight check).
    -   **Calls:** `WhatsAppService` (to actually execute start/stop commands).
    -   **Why?** Centralizes the "Aggressive Lifecycle" logic so that Jobs and Controllers don't need to know _how_ to save RAM, just that they need a session.

### 3. `app/Services/WhatsAppService.php`

-   **Role:** The HTTP Client wrapper for the `whatsapp-server` (Node.js).
-   **Key Logic:**
    -   `startSession()`, `getQrCode()`, `sendMessage()`.
    -   It formats raw Guzzle HTTP requests to the Node.js API endpoints.
-   **Relationships:**
    -   **Called By:** `SessionManager`, `AuthController`.
    -   **Why?** Abstraction layer. If the Node.js API changes, we only update this file, not every controller.

### 4. `app/Http/Middleware/EnsureWhatsAppConnected.php`

-   **Role:** Guard for routes that require an active connection (e.g., creating a campaign).
-   **Logic:** Checks `user->session_state`. If disconnected, aborts request.

---

## 📢 Module B: Campaigns & Quotas

The core business logic for sending messages.

### 1. `app/Http/Controllers/CampaignController.php`

-   **Role:** Handles the Campaign UI, file uploads, and initial dispatch.
-   **Key Logic:**
    -   `send()`:
        1.  **Locking:** Acquires `campaign_send_lock` to prevent double-clicks.
        2.  **Quota:** Calls `CampaignQuotaService->reserveQuota()`. If failed, stops.
        3.  **Collage:** If multiple images, calls `ImageCollageService`.
        4.  **Dispatch:** Loops through contacts and dispatches `SendWhatsappCampaign` job for each.
-   **Relationships:**
    -   **Calls:** `CampaignQuotaService`, `SendWhatsappCampaign` (Job).
    -   **Why?** The controller only _validates_ and _schedules_. It does NOT send messages. This ensures the UI returns instantly (Response Time < 200ms) even for large campaigns.

### 2. `app/Jobs/SendWhatsappCampaign.php`

-   **Role:** The background worker that actually sends the message.
-   **Key Logic:**
    -   **Wake-on-Demand:** It calls `SessionManager->wakeSession()` before sending. If the session is sleeping, it wakes it up.
    -   **Throttling:** It has a built-in delay (15s) logic if configured.
    -   **Cleanup:** If `isLastInBatch` is true, it calls `SessionManager->closeSession()` to free RAM.
-   **Relationships:**
    -   **Called By:** `CampaignController`.
    -   **Calls:** `WhatsAppService` (to send), `SessionManager` (to wake/sleep).
    -   **Why?** Moving sending to a Job prevents PHP timeouts and allows the "Wake-on-Demand" lifecycle to work without freezing the user's browser.

### 3. `app/Services/CampaignQuotaService.php`

-   **Role:** Enforces message limits (e.g., 100/day).
-   **Key Logic:**
    -   `reserveQuota()`: Uses **Redis Atomic Operations** (Lua Script). It checks limit AND increments counter in a single millisecond operation.
    -   **Fallback:** If Redis is down, it uses MySQL `lockForUpdate()` (slower but safe).
-   **Relationships:**
    -   **Relations:** `CampaignQuota` (Model), `User`.
    -   **Why?** Strict enforcement is needed to prevent WhatsApp bans. Atomic operations are the only way to do this reliably with concurrent users.

### 4. `app/Models/CampaignQuota.php`

-   **Role:** Database model for persisting usage data.
-   **Fields:** `contacts_sent`, `window_ends_at`.
-   **Logic:** `isWindowExpired()` checks if the 24-hour window has passed to reset the counter.

---

## 👥 Module C: Contacts Management

Sophisticated import and management of contact lists.

### 1. `app/Http/Controllers/ContactController.php`

-   **Role:** Contacts CRUD and Import wizard.
-   **Key Logic:**
    -   `previewImport()`: Uses `PhpSpreadsheet` to read Excel. Tries to auto-detect columns ("Name", "Mobile").
    -   `confirmImport()`:
        1.  Reads cached preview data.
        2.  Performs **Batch Insert** (500 rows/query) for speed.
        3.  Checks `User->remaining_contact_slots`.
-   **Relationships:**
    -   **Calls:** `ContactsImport`.
    -   **Why?** The two-step import (Preview -> Confirm) prevents users from uploading bad data or messing up column mapping.

### 2. `app/Imports/ContactsImport.php`

-   **Role:** Service class for parsing Excel files.
-   **Logic:** Defines validation rules (e.g., valid phone format) for each row.

### 3. `app/Models/Contact.php`

-   **Role:** Model representing a single contact.
-   **Fields:** `phone`, `name`, `last_sent_at` (used for filtering).

---

## 💳 Module D: Subscription & Payments

Handles access control and monetization.

### 1. `app/Http/Controllers/SubscriptionController.php`

-   **Role:** UI for viewing plans and uploading payment receipts.
-   **Logic:** `submitPayment()` handles image upload for offline payments (Vodafone Cash/Instapay).

### 2. `app/Models/PaymentRequest.php`

-   **Role:** Stores payment proofs.
-   **Status:** `pending` -> `approved` (activates sub) or `rejected`.

### 3. `app/Http/Middleware/EnsureSubscriptionActive.php`

-   **Role:** Global guard.
-   **Logic:** Checks `User->activeSubscription()`. If expired, redirects to subscription page.
-   **Why?** Applied globally in `routes/web.php` to ensure no one uses the system for free.

---

## 🛠️ Module E: Admin Panel

Separate high-privilege area for management.

### 1. `routes/admin.php`

-   **Role:** Defines routes prefixed with `/admin`.
-   **Security:** Uses `admin.auth` middleware (separate guard from users).

### 2. `app/Http/Controllers/Admin/AdminUsersController.php`

-   **Role:** User management.
-   **Actions:** Suspend user, Force-activate subscription, View stats.

### 3. `app/Http/Controllers/Admin/AdminPaymentRequestsController.php`

-   **Role:** Reviewing payment receipts.
-   **Logic:** `approve()` method automatically creates a `Subscription` record for the user and notifies them.

---

## ⚙️ System Maintenance

### 1. `app/Console/Commands/CleanupSessionsCommand.php`

-   **Role:** Scheduled task (Cron).
-   **Logic:** Calls `SessionManager->closeIdleSessions()`. Runs every minute to ensure no "Zombie Sessions" allow RAM to leak.

### 2. `app/Console/Commands/CheckExpiredSubscriptions.php`

-   **Role:** Scheduled task.
-   **Logic:** Marks subscriptions as expired and notifies users.

---

## 📝 Summary of Relationships

-   **User** ↔ **Session**: 1-to-1 relationship, managed by `SessionManager` state machine.
-   **User** ↔ **Campaign**: Users trigger `CampaignController`, which locks the user and dispatches `SendWhatsappCampaign` jobs.
-   **Job** ↔ **WhatsApp**: Messages are sent via `WhatsAppService`, which talks to the Node.js microservice.
-   **Quota** ↔ **Redis**: Usage is tracked in Redis for speed and synced to MySQL for persistence.

This architecture ensures **High Performance** (via Jobs/Redis) and **High Reliability** (via Atomic Locks/Quotas) on limited hardware.

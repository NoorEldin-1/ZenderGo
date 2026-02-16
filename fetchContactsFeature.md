# Fetch My Contacts Feature

## Goal Description
Implement a feature to allow users to fetch all their WhatsApp contacts and store them in the system. This involves checking the WhatsApp connection, fetching contacts from the Node.js server, and saving them to the Laravel database.

## User Review Required
> [!IMPORTANT]
> **RAM Usage Consideration**: We will implement a lightweight in-memory contact storage in the Node.js server. This will slightly increase RAM usage per session, but it is necessary to provide the "Fetch Contacts" capability without storing full message history.

## Proposed Changes

### Node.js Server (`whatsapp-server`)

#### [MODIFY] [BaileysSession.ts](file:///c:/work/projects/zender/whatsapp-server/src/services/BaileysSession.ts)
- Update [SessionInfo](file:///c:/work/projects/zender/whatsapp-server/src/services/BaileysSession.ts#49-59) interface to include `contacts: { [id: string]: any }`.
- In [startSession](file:///c:/work/projects/zender/app/Services/WhatsAppService.php#139-233), initialize `contacts` object.
- In [setupEventHandlers](file:///c:/work/projects/zender/whatsapp-server/src/services/BaileysSession.ts#342-514), listen to `contacts.upsert` and `contacts.update` events to populate the `contacts` map.
- Add `getContacts(sessionId)` method to return the contacts list.

#### [MODIFY] [routes/index.ts](file:///c:/work/projects/zender/whatsapp-server/src/routes/index.ts)
- Add `GET /api/:session/contacts` endpoint.
- Calls `sessionManager.getContacts(session)` and returns the list.

### Laravel Backend

#### [MODIFY] [WhatsAppService.php](file:///c:/work/projects/zender/app/Services/WhatsAppService.php)
- Add `fetchContacts()` method.
- Makes HTTP GET request to `{$this->baseUrl}/api/{$this->session}/contacts`.

#### [MODIFY] [ContactController.php](file:///c:/work/projects/zender/app/Http/Controllers/ContactController.php)
- Add `fetchFromWhatsapp(Request $request)` method.
- **Step 1**: Check connection (reusing existing logic or via service).
- **Step 2**: Call `WhatsAppService->fetchContacts()`.
- **Step 3**: Iterate and store contacts:
    - Normalize phone numbers (remove `@s.whatsapp.net`, standardizing).
    - Check duplicates.
    - Check contact limits.
    - Bulk insert/update.
- Return JSON response with counts (added, skipped, errors).

#### [MODIFY] [routes/web.php](file:///c:/work/projects/zender/routes/web.php)
- Add `POST /contacts/fetch` route pointing to `ContactController@fetchFromWhatsapp`.

### Frontend

#### [MODIFY] [contacts/index.blade.php](file:///c:/work/projects/zender/resources/views/contacts/index.blade.php)
- **UI**: Add "Fetch My Contacts" button in the header (near "Add Contact").
    - Button attributes: `id="btnFetchContacts"`.
- **Modals**:
    - Add `confirmFetchModal`: Simple confirmation.
    - Add `loadingFetchModal`: Full-screen, non-closable, with progress steps.
        - Step 1: Checking connection...
        - Step 2: Fetching contacts...
        - Step 3: Saving...
- **JS**:
    - Event listener for button click -> Show Confirm Modal.
    - Confirm -> Show Loading Modal -> Ajax call to `contacts.fetch`.
    - Handle 404/Disconnected: Redirect to reconnect page.
    - Handle Success: Show success message and reload.

## Verification Plan

### Automated Tests
- None available for this specific integration.

### Manual Verification
1.  **Frontend**:
    - Go to Contacts page.
    - Click "Fetch My Contacts".
    - Verify Confirmation Modal appears.
    - Click "Yes".
    - Verify Loading Modal appears and covers screen.
2.  **Connection Flow**:
    - **Scenario A (Connected)**:
        - Ensure WhatsApp is connected.
        - Verify process completes.
        - Verify contacts are added to the list.
    - **Scenario B (Disconnected)**:
        - Logout from WhatsApp on mobile.
        - Click Fetch.
        - Verify redirection to Reconnect page.
3.  **Data Verification**:
    - Check if names and phone numbers are imported correctly.
    - Check if duplicates are handled (not added twice).

{{-- Mobile Floating Bulk Action Bar - Only visible on screens < 768px when contacts are selected --}}
<div id="mobileBulkActionBar" class="mobile-action-bar d-md-none" aria-hidden="true">
    <div class="d-flex align-items-center justify-content-between w-100 gap-3">
        {{-- Left: Selection Count --}}
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                <span id="mobileSelectedCount">0</span>
            </span>
            <span class="text-white-50 small">محدد</span>
        </div>

        {{-- Right: Action Buttons --}}
        <div class="d-flex gap-2">
            {{-- Clear Selection --}}
            <button type="button" class="action-btn btn-clear" id="mobileClearSelectionBtn" title="إلغاء التحديد">
                <i class="bi bi-x-lg"></i>
            </button>

            {{-- Share --}}
            <button type="button" class="action-btn btn-share" id="mobileShareBtn" data-bs-toggle="modal"
                data-bs-target="#shareModal" title="مشاركة">
                <i class="bi bi-share-fill"></i>
            </button>

            {{-- Delete --}}
            <button type="button" class="action-btn btn-delete" id="mobileBulkDeleteBtn" title="حذف">
                <i class="bi bi-trash3-fill"></i>
            </button>
        </div>
    </div>
</div>

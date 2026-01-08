@extends('layouts.app')

@section('title', 'حملة جديدة')

@section('content')
    <div class="mb-3">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-megaphone text-success me-2"></i>حملة جديدة
        </h2>
        <p class="text-muted small mb-0">أرسل رسائل جماعية عبر واتساب</p>
    </div>

    <form action="{{ route('campaigns.send') }}" method="POST" enctype="multipart/form-data" id="campaignForm">
        @csrf

        <div class="row g-3">
            <!-- Contact Selection -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold small">اختر المستلمين <span class="text-danger small">(max 10)</span></h6>
                        <span class="badge bg-primary" id="selectedCount">0 / 10</span>
                    </div>

                    <div class="p-2 border-bottom">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm border-start-0" id="contactSearch"
                                placeholder="بحث بالاسم أو الرقم...">
                        </div>
                    </div>

                    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllPage">
                            <label class="form-check-label small fw-semibold" for="selectAllPage">
                                تحديد الكل في هذه الصفحة
                            </label>
                        </div>
                    </div>

                    <!-- Contacts List Container -->
                    <div class="card-body p-0 position-relative" style="height: 380px; overflow-y: auto;"
                        id="contactsListContainer">
                        <div id="loadingIndicator"
                            class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column justify-content-center align-items-center d-none"
                            style="z-index: 10;">
                            <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                            <span class="small text-muted">جاري التحميل...</span>
                        </div>

                        <div id="contactsList">
                            <!-- JS will inject contacts here -->
                        </div>

                        <div class="text-center py-5 d-none" id="noSearchResults">
                            <i class="bi bi-search text-muted fs-1 opacity-50"></i>
                            <p class="text-muted small mb-0 mt-2">لا توجد نتائج</p>
                        </div>

                        <div class="text-center py-5 d-none" id="emptyState">
                            <i class="bi bi-people text-muted fs-1 opacity-50"></i>
                            <p class="text-muted small mt-2 mb-3">لا توجد جهات اتصال</p>
                            <a href="{{ route('contacts.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>إضافة جهات
                            </a>
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="card-footer bg-white p-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPageBtn" disabled>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <span class="small text-muted" id="paginationInfo">...</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPageBtn" disabled>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs container for form submission (Moved inside col-lg-5 to preserve grid layout) -->
                <div id="selectedContactsInputs"></div>
            </div>

            <!-- Message Composer -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold small">
                            <i class="bi bi-pencil-square text-primary me-1"></i>كتابة الرسالة
                        </h6>
                        <span class="badge bg-light text-dark" id="charCount">0 / 4096</span>
                    </div>
                    <div class="card-body p-0">
                        <!-- Toolbar -->
                        <div class="d-flex align-items-center gap-1 p-2 border-bottom bg-light flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="emojiBtn" title="إيموجي">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="boldBtn" title="عريض">
                                <i class="bi bi-type-bold"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="italicBtn" title="مائل">
                                <i class="bi bi-type-italic"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="strikeBtn" title="مشطوب">
                                <i class="bi bi-type-strikethrough"></i>
                            </button>
                            <div class="vr mx-1"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="insertNameBtn"
                                title="إدراج اسم المستلم">
                                <i class="bi bi-person-badge me-1"></i>اسم الشخص
                            </button>
                            <div class="vr mx-1"></div>
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#templatesModal">
                                <i class="bi bi-lightning me-1"></i>القوالب
                            </button>
                        </div>

                        <!-- Emoji Picker -->
                        <div class="emoji-picker p-2 border-bottom bg-white d-none" id="emojiPicker">
                            <div class="emoji-categories d-flex gap-1 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary active emoji-cat-btn"
                                    data-cat="popular">⭐</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary emoji-cat-btn"
                                    data-cat="faces">😀</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary emoji-cat-btn"
                                    data-cat="gestures">👍</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary emoji-cat-btn"
                                    data-cat="symbols">❤️</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary emoji-cat-btn"
                                    data-cat="objects">🎁</button>
                            </div>
                            <div class="emoji-grid" id="emojiGrid"></div>
                        </div>

                        <!-- Rich Text Editor with Contenteditable -->
                        <div class="message-editor-container">
                            <div class="rich-message-editor" id="messageEditor" contenteditable="true"
                                data-placeholder="اكتب رسالتك هنا... ✍️"></div>
                        </div>
                        <textarea class="d-none" id="message" name="message">{{ old('message') }}</textarea>

                        <!-- Multi-Image Upload Section -->
                        <div class="p-2 border-top bg-light">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <label class="btn btn-sm btn-outline-success mb-0 position-relative" for="images"
                                    id="imagesLabel">
                                    <i class="bi bi-images"></i>
                                    <span class="ms-1">صور</span>
                                    <span class="badge bg-success rounded-pill ms-1" id="imageCount">0/5</span>
                                </label>
                                <input type="file" class="d-none" id="images" name="images[]" accept="image/*"
                                    multiple>

                                <!-- Images Preview Container -->
                                <div id="imagesPreview" class="d-flex gap-2 flex-wrap flex-grow-1"></div>
                            </div>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <div class="alert alert-info py-1 px-2 mb-0 small d-flex align-items-center gap-2 flex-grow-1"
                                    id="imageHint">
                                    <i class="bi bi-lightbulb text-warning"></i>
                                    <span>يمكنك رفع <strong>حتى 5 صور</strong> مع كل رسالة (بحد أقصى 5MB لكل صورة)</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearAllImages">
                                    <i class="bi bi-x-lg me-1"></i>مسح الكل
                                </button>
                            </div>
                        </div>

                        <!-- Quota Status Widget -->
                        <div class="p-3 border-top bg-light">
                            <div class="quota-widget">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-speedometer2 text-primary"></i>
                                        <span class="fw-bold small">حد الإرسال</span>
                                    </div>
                                    <span class="badge bg-{{ $quotaStatus['status_color'] ?? 'success' }}"
                                        id="quotaRemaining">
                                        {{ $quotaStatus['remaining'] ?? 50 }} / {{ $quotaStatus['limit'] ?? 50 }} متبقي
                                    </span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $quotaStatus['status_color'] ?? 'success' }}"
                                        id="quotaProgress" role="progressbar"
                                        style="width: {{ $quotaStatus['percentage_remaining'] ?? 100 }}%"
                                        aria-valuenow="{{ $quotaStatus['percentage_remaining'] ?? 100 }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" id="quotaResetTime">
                                        <i class="bi bi-clock me-1"></i>
                                        @if ($quotaStatus['is_window_expired'] ?? true)
                                            الكوتا متاحة (لم تبدأ بعد)
                                        @else
                                            تتجدد بعد: {{ $quotaStatus['reset_in'] ?? '' }}
                                        @endif
                                    </small>
                                    <small class="text-{{ $quotaStatus['status_color'] ?? 'success' }} fw-semibold"
                                        id="quotaUsed">
                                        استخدمت {{ $quotaStatus['used'] ?? 0 }} رسالة
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="p-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>فاصل 15 ثانية</small>
                            <button type="submit" class="btn btn-whatsapp" id="sendBtn" disabled>
                                <i class="bi bi-send me-1"></i>إرسال
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Templates Modal -->
    <div class="modal fade" id="templatesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2 bg-success text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-lightning me-2"></i>القوالب
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#myTemplates">
                                <i class="bi bi-bookmark me-1"></i>قوالبي
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#defaultTemplates">
                                <i class="bi bi-collection me-1"></i>جاهزة
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pendingRequests">
                                <i class="bi bi-inbox me-1"></i>الواردة
                                <span class="badge bg-danger rounded-pill ms-1 d-none" id="pendingRequestsBadge">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- My Templates -->
                        <div class="tab-pane fade show active" id="myTemplates">
                            <div class="p-2 border-bottom bg-light d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-grow-1" id="saveCurrentBtn">
                                    <i class="bi bi-plus-lg me-1"></i>حفظ الرسالة الحالية
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm d-none"
                                    id="shareSelectedBtn">
                                    <i class="bi bi-share me-1"></i>مشاركة (<span id="shareCount">0</span>)
                                </button>
                            </div>
                            <div id="myTemplatesList" style="max-height: 250px; overflow-y: auto;">
                                <div class="text-center py-4 text-muted" id="noTemplatesMsg">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    لا توجد قوالب محفوظة
                                </div>
                            </div>
                        </div>

                        <!-- Default Templates -->
                        <div class="tab-pane fade" id="defaultTemplates">
                            <div class="list-group list-group-flush">
                                <button type="button" class="list-group-item list-group-item-action default-tpl"
                                    data-content="مرحباً 👋

نود إعلامكم بـ...

شكراً لكم! 🙏">
                                    <div class="d-flex justify-content-between">
                                        <strong>تحية + إعلان</strong>
                                        <i class="bi bi-arrow-left"></i>
                                    </div>
                                    <small class="text-muted">مرحباً 👋 نود إعلامكم...</small>
                                </button>
                                <button type="button" class="list-group-item list-group-item-action default-tpl"
                                    data-content="🔥 عرض خاص! 🔥

خصم حصري لفترة محدودة!

سارع بالحجز الآن! ⚡">
                                    <div class="d-flex justify-content-between">
                                        <strong>عرض خاص</strong>
                                        <i class="bi bi-arrow-left"></i>
                                    </div>
                                    <small class="text-muted">🔥 عرض خاص! خصم حصري...</small>
                                </button>
                                <button type="button" class="list-group-item list-group-item-action default-tpl"
                                    data-content="تذكير مهم ⏰

...

نتطلع لرؤيتكم! 😊">
                                    <div class="d-flex justify-content-between">
                                        <strong>تذكير</strong>
                                        <i class="bi bi-arrow-left"></i>
                                    </div>
                                    <small class="text-muted">تذكير مهم ⏰ نتطلع...</small>
                                </button>
                                <button type="button" class="list-group-item list-group-item-action default-tpl"
                                    data-content="شكراً لتواصلكم معنا! 🙏

نحن سعداء بخدمتكم.

تحياتنا 💚">
                                    <div class="d-flex justify-content-between">
                                        <strong>شكر وتقدير</strong>
                                        <i class="bi bi-arrow-left"></i>
                                    </div>
                                    <small class="text-muted">شكراً لتواصلكم معنا! 🙏</small>
                                </button>
                            </div>
                        </div>

                        <!-- Pending Requests -->
                        <div class="tab-pane fade" id="pendingRequests">
                            <div id="pendingRequestsList" style="max-height: 300px; overflow-y: auto;">
                                <div class="text-center py-4 text-muted" id="noPendingRequestsMsg">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    لا توجد طلبات مشاركة
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Templates Modal -->
    <div class="modal fade" id="shareTemplatesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2 bg-primary text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-share me-1"></i>مشاركة القوالب
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">رقم هاتف المستلم</label>
                        <input type="text" class="form-control" id="sharePhoneInput" placeholder="01XXXXXXXXX"
                            dir="ltr">
                        <div class="form-text">أدخل رقم هاتف المستخدم الذي تريد مشاركة القوالب معه</div>
                    </div>
                    <div class="alert alert-info py-2 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        سيتم إرسال <strong id="shareCountInfo">0</strong> قالب للمستلم
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirmShareBtn">
                        <i class="bi bi-send me-1"></i>إرسال
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div class="modal fade" id="saveTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-bookmark-plus text-success me-1"></i>حفظ قالب جديد
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="newTemplateName" placeholder="اسم القالب"
                        maxlength="100" autofocus>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-success btn-sm" id="confirmSaveBtn">
                        <i class="bi bi-check me-1"></i>حفظ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Template Modal -->
    <div class="modal fade" id="deleteTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 64px; height: 64px;">
                        <i class="bi bi-trash text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-2">حذف القالب؟</h6>
                    <p class="text-muted small mb-0" id="deleteTemplateInfo">سيتم حذف هذا القالب نهائياً</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i>حذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        id="alertIconWrapper" style="width: 64px; height: 64px;">
                        <i class="bi" id="alertIcon" style="font-size: 1.5rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-2" id="alertTitle">تنبيه</h6>
                    <p class="text-muted small mb-0" id="alertMessage"></p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">حسناً</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .contact-item {
            cursor: pointer;
            transition: background 0.15s;
        }

        .contact-item:hover {
            background-color: #f8f9fa;
        }

        .contact-item:has(.form-check-input:checked) {
            background-color: rgba(37, 211, 102, 0.08);
        }

        .contact-item.hidden {
            display: none !important;
        }

        /* Template Selection Styling - Enhanced Visibility */
        .template-item {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .template-item:hover {
            background-color: #f0f9f4;
        }

        .template-item:has(.tpl-checkbox:checked) {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.15) 0%, rgba(37, 211, 102, 0.08) 100%);
            border-right: 4px solid #25d366 !important;
        }

        .template-item .tpl-checkbox {
            width: 22px;
            height: 22px;
            border: 2px solid #25d366;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .template-item .tpl-checkbox:checked {
            background-color: #25d366;
            border-color: #25d366;
        }

        .template-item .tpl-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.25);
        }

        .message-textarea {
            resize: none;
            font-size: 0.95rem;
            line-height: 1.6;
            padding: 0.75rem;
        }

        .message-textarea:focus {
            box-shadow: none;
            background: #fafffe;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 2px;
            max-height: 100px;
            overflow-y: auto;
        }

        .emoji-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: none;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 4px;
        }

        .emoji-btn:hover {
            background: #f0f0f0;
            transform: scale(1.1);
        }

        .emoji-cat-btn.active {
            background: var(--whatsapp-green) !important;
            border-color: var(--whatsapp-green) !important;
            color: white !important;
        }

        .template-item {
            transition: background 0.15s;
        }

        .template-item:hover {
            background: #f8f9fa;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--whatsapp-green);
            border-bottom-color: var(--whatsapp-green);
        }

        /* Rich Message Editor Container Styles */
        .message-editor-container {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 0;
            transition: all 0.3s ease;
        }

        .message-editor-container:focus-within {
            background: linear-gradient(135deg, #f0fff8 0%, #e8fff4 100%);
            box-shadow: inset 0 0 0 2px rgba(37, 211, 102, 0.15);
        }

        .message-editor-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #25d366 0%, #128c7e 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .message-editor-container:focus-within::before {
            opacity: 1;
        }

        /* Rich Message Editor Styles */
        .rich-message-editor {
            min-height: 280px;
            max-height: 400px;
            overflow-y: auto;
            padding: 1rem 1.25rem;
            font-size: 1.05rem;
            line-height: 1.8;
            outline: none;
            direction: rtl;
            white-space: pre-wrap;
            word-wrap: break-word;
            background: transparent;
            color: #1a1a1a;
            letter-spacing: 0.01em;
        }

        .rich-message-editor:empty::before {
            content: attr(data-placeholder);
            color: #9e9e9e;
            pointer-events: none;
            font-style: italic;
        }

        .rich-message-editor:focus {
            background: transparent;
        }

        /* Custom Scrollbar for Editor */
        .rich-message-editor::-webkit-scrollbar {
            width: 6px;
        }

        .rich-message-editor::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .rich-message-editor::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }

        .rich-message-editor::-webkit-scrollbar-thumb:hover {
            background: #25d366;
        }

        .name-placeholder {
            background: linear-gradient(135deg, #00c853 0%, #00a844 100%);
            color: white;
            padding: 2px 10px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.9em;
            display: inline-block;
            margin: 0 2px;
            cursor: default;
            user-select: all;
        }

        .name-placeholder::before {
            content: "👤 ";
        }

        /* Multi-Image Preview Styles */
        .image-preview-item {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 4px 8px;
            gap: 6px;
            transition: all 0.2s ease;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .image-preview-item:hover {
            border-color: #25d366;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.2);
        }

        .image-preview-item img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 6px;
        }

        .image-preview-item .image-name {
            max-width: 80px;
            font-size: 0.75rem;
            color: #495057;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-preview-item .remove-image-btn {
            background: none;
            border: none;
            color: #dc3545;
            padding: 0;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            opacity: 0.7;
            transition: all 0.2s;
        }

        .image-preview-item .remove-image-btn:hover {
            opacity: 1;
            transform: scale(1.2);
        }

        #imagesLabel {
            transition: all 0.2s ease;
        }

        #imagesLabel:hover {
            background-color: #25d366;
            border-color: #25d366;
            color: white;
        }

        #imagesLabel:hover .badge {
            background-color: white !important;
            color: #25d366 !important;
        }

        /* Quota Widget Styles */
        .quota-widget {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .quota-widget .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .quota-widget .progress-bar {
            transition: width 0.5s ease, background-color 0.3s ease;
            border-radius: 10px;
        }

        .quota-widget .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        /* Quota warning animations */
        @keyframes quotaPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .quota-warning {
            animation: quotaPulse 1.5s ease-in-out infinite;
        }

        .quota-danger {
            animation: quotaPulse 0.8s ease-in-out infinite;
        }

        /* Quota exceeded state */
        .quota-exceeded {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%) !important;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .quota-exceeded .badge {
            background-color: #dc3545 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const messageTextarea = document.getElementById('message');
            const messageEditor = document.getElementById('messageEditor');
            const sendBtn = document.getElementById('sendBtn');
            const templatesModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('templatesModal'));
            const saveTemplateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('saveTemplateModal'));
            const deleteTemplateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                'deleteTemplateModal'));
            const alertModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('alertModal'));

            // --- State Management ---
            const state = {
                selectedContacts: new Set(), // Stores IDs
                currentPage: 1,
                lastPage: 1,
                isLoading: false,
                searchQuery: '',
                limit: 10,
                // Quota tracking
                quota: {
                    limit: {{ $quotaStatus['limit'] ?? 100 }},
                    remaining: {{ $quotaStatus['remaining'] ?? 100 }},
                    used: {{ $quotaStatus['used'] ?? 0 }},
                    percentageRemaining: {{ $quotaStatus['percentage_remaining'] ?? 100 }},
                    statusColor: '{{ $quotaStatus['status_color'] ?? 'success' }}',
                    resetIn: '{{ $quotaStatus['reset_in'] ?? '' }}',
                    isExpired: {{ $quotaStatus['is_window_expired'] ?? true ? 'true' : 'false' }}
                },
                // Serial Batch: Campaign status tracking
                campaign: {
                    active: false,
                    sent: 0,
                    total: 0
                }
            };

            // --- Selectors ---
            const els = {
                contactsList: document.getElementById('contactsList'),
                loading: document.getElementById('loadingIndicator'),
                noResults: document.getElementById('noSearchResults'),
                empty: document.getElementById('emptyState'),
                search: document.getElementById('contactSearch'),
                selectAllPage: document.getElementById('selectAllPage'),
                selectedCount: document.getElementById('selectedCount'),
                prevBtn: document.getElementById('prevPageBtn'),
                nextBtn: document.getElementById('nextPageBtn'),
                pageInfo: document.getElementById('paginationInfo'),
                inputsContainer: document.getElementById('selectedContactsInputs')
            };

            // --- Helper Functions ---
            function showAlert(message, type = 'warning') {
                const wrapper = document.getElementById('alertIconWrapper');
                const icon = document.getElementById('alertIcon');
                const title = document.getElementById('alertTitle');

                wrapper.className = 'rounded-circle d-inline-flex align-items-center justify-content-center mb-3';
                icon.className = 'bi';

                if (type === 'warning') {
                    wrapper.classList.add('bg-warning', 'bg-opacity-10');
                    icon.classList.add('bi-exclamation-triangle', 'text-warning');
                    title.textContent = 'تنبيه';
                } else if (type === 'error') {
                    wrapper.classList.add('bg-danger', 'bg-opacity-10');
                    icon.classList.add('bi-x-circle', 'text-danger');
                    title.textContent = 'خطأ';
                } else if (type === 'success') {
                    wrapper.classList.add('bg-success', 'bg-opacity-10');
                    icon.classList.add('bi-check-circle', 'text-success');
                    title.textContent = 'تم';
                } else {
                    wrapper.classList.add('bg-primary', 'bg-opacity-10');
                    icon.classList.add('bi-info-circle', 'text-primary');
                    title.textContent = 'معلومة';
                }

                document.getElementById('alertMessage').textContent = message;
                alertModal.show();
            }

            // --- Quota Management Functions ---
            function updateQuotaUI() {
                const quotaRemaining = document.getElementById('quotaRemaining');
                const quotaProgress = document.getElementById('quotaProgress');
                const quotaResetTime = document.getElementById('quotaResetTime');
                const quotaUsed = document.getElementById('quotaUsed');
                const quotaWidget = document.querySelector('.quota-widget');

                // Update text
                quotaRemaining.textContent = `${state.quota.remaining} / ${state.quota.limit} متبقي`;
                quotaUsed.textContent = `استخدمت ${state.quota.used} رسالة`;

                // Update progress bar
                quotaProgress.style.width = `${state.quota.percentageRemaining}%`;

                // Update colors based on status
                quotaRemaining.className = `badge bg-${state.quota.statusColor}`;
                quotaProgress.className = `progress-bar bg-${state.quota.statusColor}`;
                quotaUsed.className = `text-${state.quota.statusColor} fw-semibold`;

                // Update reset time
                if (state.quota.isExpired) {
                    quotaResetTime.innerHTML = '<i class="bi bi-clock me-1"></i>الكوتا متاحة (لم تبدأ بعد)';
                } else {
                    quotaResetTime.innerHTML = `<i class="bi bi-clock me-1"></i>تتجدد بعد: ${state.quota.resetIn}`;
                }

                // Add warning/danger animations
                quotaWidget.classList.remove('quota-warning', 'quota-danger', 'quota-exceeded');
                if (state.quota.remaining === 0) {
                    quotaWidget.classList.add('quota-exceeded');
                } else if (state.quota.percentageRemaining <= 10) {
                    quotaWidget.classList.add('quota-danger');
                } else if (state.quota.percentageRemaining <= 25) {
                    quotaWidget.classList.add('quota-warning');
                }
            }

            function checkQuotaLimit() {
                const selectedCount = state.selectedContacts.size;
                const exceeds = selectedCount > state.quota.remaining;

                if (exceeds && state.quota.remaining > 0) {
                    showAlert(
                        `لا يمكنك اختيار أكثر من ${state.quota.remaining} مستلم. الكوتا المتبقية: ${state.quota.remaining}`,
                        'warning');
                    return false;
                } else if (exceeds && state.quota.remaining === 0) {
                    showAlert(`تجاوزت حد الإرسال. الكوتا تتجدد بعد ${state.quota.resetIn}`, 'error');
                    return false;
                }
                return true;
            }

            async function refreshQuota() {
                try {
                    const response = await fetch('{{ route('campaigns.quota') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    state.quota = {
                        limit: data.limit,
                        remaining: data.remaining,
                        used: data.used,
                        percentageRemaining: data.percentage_remaining,
                        statusColor: data.status_color,
                        resetIn: data.reset_in,
                        isExpired: data.is_window_expired
                    };

                    updateQuotaUI();
                    updateSendButtonState();
                } catch (error) {
                    console.error('Error refreshing quota:', error);
                }
            }

            // --- SERIAL BATCH: Campaign Status Polling ---
            async function pollCampaignStatus() {
                try {
                    const response = await fetch('{{ route('campaigns.status') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    state.campaign.active = data.active;
                    state.campaign.sent = data.sent;
                    state.campaign.total = data.total;

                    updateCampaignUI();

                    // Continue polling while active
                    if (data.active) {
                        setTimeout(pollCampaignStatus, 5000);
                    } else {
                        // Campaign completed - refresh quota
                        refreshQuota();
                    }
                } catch (error) {
                    console.error('Error polling campaign status:', error);
                }
            }

            function updateCampaignUI() {
                if (state.campaign.active) {
                    sendBtn.disabled = true;
                    const progress = state.campaign.total > 0 ?
                        `(${state.campaign.sent}/${state.campaign.total})` :
                        '';
                    sendBtn.innerHTML =
                        `<i class="bi bi-hourglass-split me-1 spin-slow"></i>جاري الإرسال... ${progress}`;
                    sendBtn.classList.remove('btn-whatsapp', 'btn-danger');
                    sendBtn.classList.add('btn-secondary');
                    sendBtn.title = 'يرجى انتظار انتهاء الحملة الحالية قبل إرسال دفعة جديدة';

                    // Also disable contact selection during active campaign
                    els.selectAllPage.disabled = true;
                } else {
                    // Restore normal button state
                    els.selectAllPage.disabled = false;
                    sendBtn.title = '';
                    updateSendButtonState();
                }
            }

            // --- Core Logic: Fetch Contacts ---
            async function fetchContacts(page = 1) {
                if (state.isLoading) return;
                state.isLoading = true;
                state.currentPage = page;

                els.loading.classList.remove('d-none');
                els.contactsList.innerHTML = '';
                els.noResults.classList.add('d-none');
                els.empty.classList.add('d-none');

                try {
                    const params = new URLSearchParams({
                        page: page,
                        q: state.searchQuery
                    });

                    const response = await fetch(`{{ route('campaigns.create') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    state.lastPage = data.last_page;
                    renderContacts(data.data);
                    updatePaginationUI(data);

                } catch (error) {
                    console.error('Error fetching contacts:', error);
                    showAlert('حدث خطأ أثناء تحميل جهات الاتصال', 'error');
                } finally {
                    state.isLoading = false;
                    els.loading.classList.add('d-none');
                }
            }

            function renderContacts(contacts) {
                if (contacts.length === 0) {
                    if (state.searchQuery) {
                        els.noResults.classList.remove('d-none');
                    } else {
                        els.empty.classList.remove('d-none');
                    }
                    return;
                }

                els.contactsList.innerHTML = contacts.map(contact => `
                    <label class="contact-item d-block px-3 py-2 border-bottom m-0">
                        <div class="form-check d-flex align-items-center gap-2 m-0">
                            <input type="checkbox" class="form-check-input contact-checkbox mt-0"
                                value="${contact.id}" 
                                ${state.selectedContacts.has(String(contact.id)) ? 'checked' : ''}>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-medium small text-truncate">${escapeHtml(contact.name)}</div>
                                <small class="text-muted" dir="ltr">${escapeHtml(contact.phone)}</small>
                            </div>
                        </div>
                    </label>
                `).join('');

                // Attach event listeners to new checkboxes
                document.querySelectorAll('.contact-checkbox').forEach(cb => {
                    cb.addEventListener('change', (e) => toggleSelection(e.target));
                });

                updateSelectAllState();
            }

            function toggleSelection(checkbox) {
                const id = checkbox.value;

                if (checkbox.checked) {
                    // Check per-request limit (50)
                    if (state.selectedContacts.size >= state.limit) {
                        checkbox.checked = false;
                        showAlert(`عفواً، الحد الأقصى للمستلمين هو ${state.limit} مستلم فقط للحفاظ على استقرار الخدمة.`,
                            'warning');
                        return;
                    }
                    // Check quota limit
                    if (state.selectedContacts.size >= state.quota.remaining) {
                        checkbox.checked = false;
                        if (state.quota.remaining === 0) {
                            showAlert(`تجاوزت حد الإرسال. الكوتا تتجدد بعد ${state.quota.resetIn}`, 'error');
                        } else {
                            showAlert(
                                `الكوتا المتبقية ${state.quota.remaining} رسالة فقط. تتجدد بعد ${state.quota.resetIn}`,
                                'warning');
                        }
                        return;
                    }
                    state.selectedContacts.add(id);
                } else {
                    state.selectedContacts.delete(id);
                }

                updateUIState();
            }

            function updateUIState() {
                // Update Badge - show both selected and quota remaining
                const quotaLimited = Math.min(state.limit, state.quota.remaining);
                els.selectedCount.textContent = `${state.selectedContacts.size} / ${quotaLimited}`;

                // Change badge color based on quota
                if (state.quota.remaining === 0) {
                    els.selectedCount.classList.remove('bg-primary');
                    els.selectedCount.classList.add('bg-danger');
                } else if (state.selectedContacts.size >= state.quota.remaining) {
                    els.selectedCount.classList.remove('bg-primary');
                    els.selectedCount.classList.add('bg-warning');
                } else {
                    els.selectedCount.classList.remove('bg-danger', 'bg-warning');
                    els.selectedCount.classList.add('bg-primary');
                }

                // Update Hidden Inputs for Server
                els.inputsContainer.innerHTML = Array.from(state.selectedContacts).map(id =>
                    `<input type="hidden" name="contacts[]" value="${id}">`
                ).join('');

                // Sync editor content to hidden textarea
                syncEditorToTextarea();

                // Enable/Disable Send Button (with quota check)
                updateSendButtonState();

                // Update Select All Checkbox logic
                updateSelectAllState();
            }

            function updateSendButtonState() {
                // If campaign is active, don't touch button state - handled by updateCampaignUI
                if (state.campaign.active) {
                    return;
                }

                const hasMessage = messageTextarea.value.trim().length > 0;
                const hasContacts = state.selectedContacts.size > 0;
                const withinQuota = state.selectedContacts.size <= state.quota.remaining;

                sendBtn.disabled = !hasMessage || !hasContacts || !withinQuota;

                // Update button text if quota exceeded
                if (hasContacts && !withinQuota) {
                    sendBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>تجاوز الحد';
                    sendBtn.classList.remove('btn-whatsapp');
                    sendBtn.classList.add('btn-danger');
                } else {
                    sendBtn.innerHTML = '<i class="bi bi-send me-1"></i>إرسال';
                    sendBtn.classList.remove('btn-danger');
                    sendBtn.classList.add('btn-whatsapp');
                }
            }

            function updateSelectAllState() {
                const checkboxes = document.querySelectorAll('.contact-checkbox');
                if (checkboxes.length === 0) {
                    els.selectAllPage.checked = false;
                    els.selectAllPage.disabled = true;
                    return;
                }

                els.selectAllPage.disabled = false;
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                els.selectAllPage.checked = allChecked;
            }

            function updatePaginationUI(data) {
                els.prevBtn.disabled = data.current_page === 1;
                els.nextBtn.disabled = data.current_page === data.last_page;
                els.pageInfo.textContent = `صفحة ${data.current_page} من ${data.last_page}`;
            }

            // --- Event Listeners ---

            // Search Debounce
            let debounceTimer;
            els.search.addEventListener('input', (e) => {
                state.searchQuery = e.target.value.trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchContacts(1);
                }, 300);
            });

            // Pagination Controls
            els.prevBtn.addEventListener('click', () => {
                if (state.currentPage > 1) fetchContacts(state.currentPage - 1);
            });

            els.nextBtn.addEventListener('click', () => {
                if (state.currentPage < state.lastPage) fetchContacts(state.currentPage + 1);
            });

            // Select All Page
            els.selectAllPage.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                const checkboxes = document.querySelectorAll('.contact-checkbox');
                let addedCount = 0;
                let blocked = false;

                checkboxes.forEach(cb => {
                    if (isChecked) {
                        if (!cb.checked) {
                            if (state.selectedContacts.size < state.limit) {
                                cb.checked = true;
                                state.selectedContacts.add(cb.value);
                                addedCount++;
                            } else {
                                blocked = true;
                            }
                        }
                    } else {
                        if (cb.checked) {
                            cb.checked = false;
                            state.selectedContacts.delete(cb.value);
                        }
                    }
                });

                if (blocked) {
                    showAlert(`تم تحديد المسموح به فقط. الحد الأقصى هو ${state.limit}.`, 'warning');
                }

                updateUIState();
            });

            // Initial Load
            fetchContacts();

            // SERIAL BATCH: Check campaign status on page load
            pollCampaignStatus();

            // === OLD LOGIC RE-INTEGRATION (Emojis, Templates, Message Area) === //

            let templateToDelete = null;

            // ========== EMOJIS ==========
            const emojis = {
                popular: ['👋', '😊', '🙏', '❤️', '🔥', '⭐', '✨', '💯', '👍', '🎉', '💪', '🚀', '💚', '📢', '⚡',
                    '🎁', '💰', '📞', '✅', '⏰'
                ],
                faces: ['😀', '😃', '😄', '😁', '😊', '🥰', '😍', '🤩', '😘', '😎', '🤗', '🙂', '😉', '😋', '🤔',
                    '😮', '😲', '🙄', '😏', '😌'
                ],
                gestures: ['👍', '👎', '👌', '✌️', '🤞', '🤝', '👏', '🙌', '💪', '🙏', '☝️', '👇', '👈', '👉', '✋',
                    '🤚', '👋', '🤙', '💅'
                ],
                symbols: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘',
                    '💝', '✨', '⭐', '🌟', '💫'
                ],
                objects: ['🎁', '🎉', '🎊', '📱', '💻', '📞', '📧', '💰', '💵', '💳', '🛒', '📦', '🏆', '🥇', '🎯',
                    '📈', '📊', '🔔', '📢', '✏️'
                ]
            };

            const emojiGrid = document.getElementById('emojiGrid');
            const emojiPicker = document.getElementById('emojiPicker');

            function showEmojis(cat) {
                emojiGrid.innerHTML = '';
                emojis[cat].forEach(e => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'emoji-btn';
                    btn.textContent = e;
                    btn.onclick = () => insertAtCursor(e);
                    emojiGrid.appendChild(btn);
                });
            }

            document.getElementById('emojiBtn')?.addEventListener('click', () => {
                emojiPicker.classList.toggle('d-none');
                if (!emojiPicker.classList.contains('d-none')) showEmojis('popular');
            });

            document.querySelectorAll('.emoji-cat-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.emoji-cat-btn').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.add('active');
                    showEmojis(this.dataset.cat);
                });
            });

            // Insert text/HTML at cursor in contenteditable
            function insertAtCursor(text, isHtml = false) {
                messageEditor.focus();
                if (isHtml) {
                    document.execCommand('insertHTML', false, text);
                } else {
                    document.execCommand('insertText', false, text);
                }
                updateUIState();
            }

            // Insert name placeholder as styled element
            function insertNamePlaceholder() {
                const placeholder = '<span class="name-placeholder" contenteditable="false">اسم المستلم</span>&nbsp;';
                messageEditor.focus();
                document.execCommand('insertHTML', false, placeholder);
                updateUIState();
            }

            // Sync contenteditable content to hidden textarea for form submission
            function syncEditorToTextarea() {
                // Get text content, replacing styled placeholders with the actual placeholder text
                let content = messageEditor.innerHTML;
                // Replace styled name placeholders with the raw placeholder
                content = content.replace(/<span class="name-placeholder"[^>]*>.*?<\/span>/g, '@{{ اسم_المستلم }}');
                // Convert HTML to plain text
                const temp = document.createElement('div');
                temp.innerHTML = content;
                // Replace <br> and </div> with newlines
                temp.innerHTML = temp.innerHTML.replace(/<br\s*\/?>/gi, '\n').replace(/<\/div>/gi, '\n');
                messageTextarea.value = temp.textContent || temp.innerText || '';
            }

            // Format
            ['bold', 'italic', 'strike'].forEach((type, i) => {
                const chars = ['*', '_', '~'];
                document.getElementById(type + 'Btn')?.addEventListener('click', () => {
                    const c = chars[i],
                        start = messageTextarea.selectionStart,
                        end = messageTextarea.selectionEnd;
                    const val = messageTextarea.value,
                        sel = val.slice(start, end);
                    messageTextarea.value = val.slice(0, start) + c + sel + c + val.slice(end);
                    messageTextarea.selectionStart = start;
                    messageTextarea.selectionEnd = end + 2;
                    messageTextarea.focus();
                    updateUIState();
                });
            });

            // Insert Name Placeholder Button
            document.getElementById('insertNameBtn')?.addEventListener('click', () => {
                insertNamePlaceholder();
            });

            // ========== TEMPLATES ==========
            const myTemplatesList = document.getElementById('myTemplatesList');
            const noTemplatesMsg = document.getElementById('noTemplatesMsg');
            const shareSelectedBtn = document.getElementById('shareSelectedBtn');
            const shareTemplatesModal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                'shareTemplatesModal'));
            const pendingRequestsBadge = document.getElementById('pendingRequestsBadge');
            const pendingRequestsList = document.getElementById('pendingRequestsList');

            // State for template selection
            const selectedTemplates = new Set();

            async function loadTemplates() {
                const res = await fetch('/templates');
                const data = await res.json();
                renderTemplates(data);
                loadPendingRequests();
            }

            function renderTemplates(templates) {
                myTemplatesList.innerHTML = '';
                selectedTemplates.clear();
                updateShareButton();

                if (!templates.length) {
                    myTemplatesList.innerHTML =
                        `<div class="text-center py-4 text-muted" id="noTemplatesMsg"><i class="bi bi-inbox fs-3 d-block mb-2"></i>لا توجد قوالب محفوظة</div>`;
                    return;
                }

                templates.forEach(t => {
                    const div = document.createElement('div');
                    div.className = 'template-item d-flex align-items-center p-2 border-bottom';
                    div.innerHTML = `
                        <input type="checkbox" class="form-check-input me-2 tpl-checkbox" data-id="${t.id}">
                        <button type="button" class="btn btn-link text-start flex-grow-1 p-0 text-decoration-none use-tpl" data-content="${escapeHtml(t.content)}">
                            <strong class="d-block">${escapeHtml(t.name)}</strong>
                            <small class="text-muted text-truncate d-block" style="max-width: 200px;">${escapeHtml(t.content.slice(0, 40))}...</small>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger del-tpl ms-2" data-id="${t.id}">
                            <i class="bi bi-trash"></i>
                        </button>`;
                    myTemplatesList.appendChild(div);
                });

                // Checkbox change handler
                myTemplatesList.querySelectorAll('.tpl-checkbox').forEach(cb => {
                    cb.onchange = () => {
                        if (cb.checked) {
                            selectedTemplates.add(cb.dataset.id);
                        } else {
                            selectedTemplates.delete(cb.dataset.id);
                        }
                        updateShareButton();
                    };
                });

                myTemplatesList.querySelectorAll('.use-tpl').forEach(btn => {
                    btn.onclick = () => {
                        const content = btn.dataset.content;
                        messageTextarea.value = content;
                        messageEditor.textContent = content;
                        templatesModal.hide();
                        updateUIState();
                    };
                });

                myTemplatesList.querySelectorAll('.del-tpl').forEach(btn => {
                    btn.onclick = () => {
                        templateToDelete = btn.dataset.id;
                        const name = btn.closest('.template-item').querySelector('strong').textContent;
                        document.getElementById('deleteTemplateInfo').innerHTML =
                            `حذف "<strong>${name}</strong>" نهائياً؟`;
                        templatesModal.hide();
                        setTimeout(() => deleteTemplateModal.show(), 200);
                    };
                });
            }

            function updateShareButton() {
                const count = selectedTemplates.size;
                document.getElementById('shareCount').textContent = count;
                if (count > 0) {
                    shareSelectedBtn.classList.remove('d-none');
                } else {
                    shareSelectedBtn.classList.add('d-none');
                }
            }

            // Share selected templates
            shareSelectedBtn?.addEventListener('click', () => {
                const count = selectedTemplates.size;
                document.getElementById('shareCountInfo').textContent = count;
                document.getElementById('sharePhoneInput').value = '';
                templatesModal.hide();
                setTimeout(() => shareTemplatesModal.show(), 200);
            });

            // Confirm share
            document.getElementById('confirmShareBtn')?.addEventListener('click', async () => {
                const phone = document.getElementById('sharePhoneInput').value.trim();
                if (!phone) {
                    shareTemplatesModal.hide();
                    setTimeout(() => showAlert('أدخل رقم الهاتف', 'warning'), 200);
                    return;
                }

                try {
                    const res = await fetch('/templates/share', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            phone: phone,
                            templates: Array.from(selectedTemplates)
                        })
                    });

                    const data = await res.json();
                    shareTemplatesModal.hide();

                    if (data.success) {
                        selectedTemplates.clear();
                        updateShareButton();
                        loadTemplates();
                        setTimeout(() => showAlert(data.message, 'success'), 200);
                    } else {
                        setTimeout(() => showAlert(data.message, 'error'), 200);
                    }
                } catch (err) {
                    shareTemplatesModal.hide();
                    setTimeout(() => showAlert('حدث خطأ أثناء الإرسال', 'error'), 200);
                }
            });

            // Load pending share requests
            async function loadPendingRequests() {
                try {
                    const res = await fetch('/templates/share/pending');
                    const requests = await res.json();
                    renderPendingRequests(requests);
                } catch (err) {
                    console.error('Error loading pending requests:', err);
                }
            }

            function renderPendingRequests(requests) {
                pendingRequestsList.innerHTML = '';

                if (!requests.length) {
                    pendingRequestsList.innerHTML =
                        `<div class="text-center py-4 text-muted" id="noPendingRequestsMsg"><i class="bi bi-inbox fs-3 d-block mb-2"></i>لا توجد طلبات مشاركة</div>`;
                    pendingRequestsBadge.classList.add('d-none');
                    return;
                }

                pendingRequestsBadge.textContent = requests.length;
                pendingRequestsBadge.classList.remove('d-none');

                requests.forEach(req => {
                    const div = document.createElement('div');
                    div.className = 'p-3 border-bottom';
                    div.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong class="d-block">${escapeHtml(req.sender?.name || 'مستخدم')}</strong>
                                <small class="text-muted" dir="ltr">${escapeHtml(req.sender?.phone || '')}</small>
                            </div>
                            <span class="badge bg-primary">${req.items?.length || 0} قالب</span>
                        </div>
                        <div class="mb-2 small text-muted">
                            ${req.items?.map(i => `<span class="badge bg-light text-dark me-1 mb-1">${escapeHtml(i.name)}</span>`).join('') || ''}
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm flex-grow-1 accept-share" data-id="${req.id}">
                                <i class="bi bi-check me-1"></i>قبول
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1 reject-share" data-id="${req.id}">
                                <i class="bi bi-x me-1"></i>رفض
                            </button>
                        </div>
                    `;
                    pendingRequestsList.appendChild(div);
                });

                // Accept handler
                pendingRequestsList.querySelectorAll('.accept-share').forEach(btn => {
                    btn.onclick = async () => {
                        btn.disabled = true;
                        try {
                            const res = await fetch(`/templates/share/${btn.dataset.id}/accept`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                showAlert(data.message, 'success');
                                loadTemplates();
                            } else {
                                showAlert(data.message, 'error');
                            }
                        } catch (err) {
                            showAlert('حدث خطأ', 'error');
                        }
                        btn.disabled = false;
                    };
                });

                // Reject handler
                pendingRequestsList.querySelectorAll('.reject-share').forEach(btn => {
                    btn.onclick = async () => {
                        btn.disabled = true;
                        try {
                            const res = await fetch(`/templates/share/${btn.dataset.id}/reject`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                loadPendingRequests();
                            }
                        } catch (err) {
                            showAlert('حدث خطأ', 'error');
                        }
                        btn.disabled = false;
                    };
                });
            }

            document.querySelectorAll('.default-tpl').forEach(btn => {
                btn.onclick = () => {
                    const content = btn.dataset.content;
                    messageTextarea.value = content;
                    messageEditor.textContent = content;
                    templatesModal.hide();
                    updateUIState();
                };
            });

            document.getElementById('saveCurrentBtn')?.addEventListener('click', () => {
                if (!messageTextarea.value.trim()) {
                    templatesModal.hide();
                    setTimeout(() => showAlert('اكتب رسالة أولاً', 'warning'), 200);
                    return;
                }
                document.getElementById('newTemplateName').value = '';
                templatesModal.hide();
                setTimeout(() => saveTemplateModal.show(), 200);
            });

            document.getElementById('confirmSaveBtn')?.addEventListener('click', async () => {
                const name = document.getElementById('newTemplateName').value.trim();
                if (!name) {
                    saveTemplateModal.hide();
                    setTimeout(() => showAlert('أدخل اسم القالب', 'warning'), 200);
                    return;
                }

                await fetch('/templates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        name,
                        content: messageTextarea.value
                    })
                });
                saveTemplateModal.hide();
                loadTemplates();
            });

            document.getElementById('confirmDeleteBtn')?.addEventListener('click', async () => {
                if (!templateToDelete) return;
                await fetch(`/templates/${templateToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                });
                templateToDelete = null;
                deleteTemplateModal.hide();
                loadTemplates();
            });

            function escapeHtml(s) {
                return s?.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') ||
                    '';
            }

            loadTemplates();

            // ========== MULTI-IMAGE HANDLER ==========
            const MAX_IMAGES = 5;
            const imagesInput = document.getElementById('images');
            const imagesPreview = document.getElementById('imagesPreview');
            const imageCount = document.getElementById('imageCount');
            const clearAllBtn = document.getElementById('clearAllImages');
            const imageHint = document.getElementById('imageHint');
            let selectedFiles = new DataTransfer();

            function updateImageCount() {
                const count = selectedFiles.files.length;
                imageCount.textContent = `${count}/${MAX_IMAGES}`;

                // Update badge color based on count
                if (count >= MAX_IMAGES) {
                    imageCount.className = 'badge bg-danger rounded-pill ms-1';
                } else if (count > 0) {
                    imageCount.className = 'badge bg-success rounded-pill ms-1';
                } else {
                    imageCount.className = 'badge bg-secondary rounded-pill ms-1';
                }

                // Show/hide clear all button
                if (count > 0) {
                    clearAllBtn.classList.remove('d-none');
                    imageHint.classList.add('d-none');
                } else {
                    clearAllBtn.classList.add('d-none');
                    imageHint.classList.remove('d-none');
                }
            }

            function renderImagePreviews() {
                imagesPreview.innerHTML = '';

                for (let i = 0; i < selectedFiles.files.length; i++) {
                    const file = selectedFiles.files[i];
                    const reader = new FileReader();

                    reader.onload = (e) => {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.dataset.index = i;
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="${escapeHtml(file.name)}">
                            <span class="image-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                            <button type="button" class="remove-image-btn" title="إزالة">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        `;

                        previewItem.querySelector('.remove-image-btn').onclick = () => {
                            removeImage(i);
                        };

                        imagesPreview.appendChild(previewItem);
                    };

                    reader.readAsDataURL(file);
                }

                updateImageCount();
            }

            function removeImage(index) {
                const newDataTransfer = new DataTransfer();

                for (let i = 0; i < selectedFiles.files.length; i++) {
                    if (i !== index) {
                        newDataTransfer.items.add(selectedFiles.files[i]);
                    }
                }

                selectedFiles = newDataTransfer;
                imagesInput.files = selectedFiles.files;
                renderImagePreviews();
            }

            function clearAllImages() {
                selectedFiles = new DataTransfer();
                imagesInput.files = selectedFiles.files;
                imagesPreview.innerHTML = '';
                updateImageCount();
            }

            imagesInput?.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                const currentCount = selectedFiles.files.length;
                const availableSlots = MAX_IMAGES - currentCount;

                if (newFiles.length > availableSlots) {
                    if (availableSlots === 0) {
                        showAlert(`تم الوصول للحد الأقصى! لا يمكنك رفع أكثر من ${MAX_IMAGES} صور.`, 'warning');
                    } else {
                        showAlert(`يمكنك إضافة ${availableSlots} صورة/صور فقط. سيتم تجاهل الباقي.`, 'warning');
                    }
                }

                const filesToAdd = newFiles.slice(0, availableSlots);

                filesToAdd.forEach(file => {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        showAlert(`الصورة "${file.name}" تتجاوز الحد الأقصى (5MB)`, 'error');
                        return;
                    }
                    selectedFiles.items.add(file);
                });

                this.files = selectedFiles.files;
                renderImagePreviews();
            });

            clearAllBtn?.addEventListener('click', () => {
                clearAllImages();
            });

            // Editor input handler
            messageEditor?.addEventListener('input', () => {
                updateUIState();
            });

            showEmojis('popular');
            updateUIState();
        })();
    </script>
@endpush

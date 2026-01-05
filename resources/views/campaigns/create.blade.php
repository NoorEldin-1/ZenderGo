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
                        <h6 class="mb-0 fw-bold small">اختر المستلمين <span class="text-danger small">(max 50)</span></h6>
                        <span class="badge bg-primary" id="selectedCount">0 / 50</span>
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

                        <!-- Image -->
                        <div class="p-2 border-top bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <label class="btn btn-sm btn-outline-secondary mb-0" for="image">
                                    <i class="bi bi-image"></i>
                                    <span class="d-none d-sm-inline ms-1">صورة</span>
                                </label>
                                <input type="file" class="d-none" id="image" name="image" accept="image/*">
                                <div id="imagePreview" class="d-none flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 bg-white rounded px-2 py-1">
                                        <img src="" alt="" class="rounded" style="height: 28px;">
                                        <span class="small text-truncate" id="imageName"
                                            style="max-width: 120px;"></span>
                                        <button type="button" class="btn btn-link text-danger p-0" id="removeImage">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
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
                    </ul>

                    <div class="tab-content">
                        <!-- My Templates -->
                        <div class="tab-pane fade show active" id="myTemplates">
                            <div class="p-2 border-bottom bg-light">
                                <button type="button" class="btn btn-success btn-sm w-100" id="saveCurrentBtn">
                                    <i class="bi bi-plus-lg me-1"></i>حفظ الرسالة الحالية كقالب
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
                    </div>
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
                limit: 50
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
                    if (state.selectedContacts.size >= state.limit) {
                        checkbox.checked = false;
                        showAlert(`عفواً، الحد الأقصى للمستلمين هو ${state.limit} مستلم فقط للحفاظ على استقرار الخدمة.`,
                            'warning');
                        return;
                    }
                    state.selectedContacts.add(id);
                } else {
                    state.selectedContacts.delete(id);
                }

                updateUIState();
            }

            function updateUIState() {
                // Update Badge
                els.selectedCount.textContent = `${state.selectedContacts.size} / ${state.limit}`;

                // Update Hidden Inputs for Server
                els.inputsContainer.innerHTML = Array.from(state.selectedContacts).map(id =>
                    `<input type="hidden" name="contacts[]" value="${id}">`
                ).join('');

                // Sync editor content to hidden textarea
                syncEditorToTextarea();

                // Enable/Disable Send Button
                const hasMessage = messageTextarea.value.trim().length > 0;
                sendBtn.disabled = state.selectedContacts.size === 0 || !hasMessage;

                // Update Select All Checkbox logic
                updateSelectAllState();
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

            async function loadTemplates() {
                const res = await fetch('/templates');
                const data = await res.json();
                renderTemplates(data);
            }

            function renderTemplates(templates) {
                myTemplatesList.innerHTML = '';
                if (!templates.length) {
                    myTemplatesList.innerHTML =
                        `<div class="text-center py-4 text-muted" id="noTemplatesMsg"><i class="bi bi-inbox fs-3 d-block mb-2"></i>لا توجد قوالب محفوظة</div>`;
                    return;
                }

                templates.forEach(t => {
                    const div = document.createElement('div');
                    div.className = 'template-item d-flex align-items-center p-2 border-bottom';
                    div.innerHTML = `
                <button type="button" class="btn btn-link text-start flex-grow-1 p-0 text-decoration-none use-tpl" data-content="${escapeHtml(t.content)}">
                    <strong class="d-block">${escapeHtml(t.name)}</strong>
                    <small class="text-muted text-truncate d-block" style="max-width: 250px;">${escapeHtml(t.content.slice(0, 50))}...</small>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger del-tpl" data-id="${t.id}">
                    <i class="bi bi-trash"></i>
                </button>`;
                    myTemplatesList.appendChild(div);
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

            // Image Handler
            document.getElementById('image')?.addEventListener('change', function() {
                if (this.files?.[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        document.querySelector('#imagePreview img').src = e.target.result;
                        document.getElementById('imageName').textContent = this.files[0].name;
                        document.getElementById('imagePreview').classList.remove('d-none');
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            document.getElementById('removeImage')?.addEventListener('click', () => {
                document.getElementById('image').value = '';
                document.getElementById('imagePreview').classList.add('d-none');
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

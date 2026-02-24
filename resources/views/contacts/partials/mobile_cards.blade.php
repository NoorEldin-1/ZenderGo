@foreach ($contacts as $contact)
    <div class="contact-card card mb-3 shadow-sm border-0" id="mobile-contact-{{ $contact->id }}"
        data-id="{{ $contact->id }}" data-name="{{ strtolower($contact->name) }}" data-phone="{{ $contact->phone }}">
        <div class="card-body p-3">
            {{-- Header: Checkbox + Name + Star --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-width-0">
                    <input type="checkbox" class="form-check-input contact-checkbox flex-shrink-0"
                        value="{{ $contact->id }}" style="width: 1.25rem; height: 1.25rem;">
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="fw-bold mb-0 text-truncate contact-name">{{ $contact->name }}</h6>
                        @if ($contact->label_text)
                            <div class="mobile-label-container mt-1">
                                <span class="badge rounded-pill"
                                    style="background-color: {{ $contact->label_color ?? '#6c757d' }}; font-size: 0.7rem; font-weight: normal;">
                                    {{ $contact->label_text }}
                                </span>
                            </div>
                        @endif
                        @if (!empty($contact->share_history))
                            <div class="mt-1">
                                <i class="bi bi-share-fill text-info me-1" style="font-size: 0.7rem;"></i>
                                @foreach ($contact->share_history as $share)
                                    <span
                                        class="badge {{ $share->status === 'accepted' ? 'bg-success' : ($share->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}"
                                        style="font-size: 0.6rem;">
                                        {{ $share->shared_with }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if ($contact->notes)
                            <div class="mt-1 text-muted small contact-note-snippet" data-id="{{ $contact->id }}"
                                style="font-size: 0.75rem;">
                                <i
                                    class="bi bi-journal-text me-1"></i>{{ Str::limit(strip_tags($contact->notes), 20) }}
                            </div>
                        @endif
                    </div>
                </div>
                <i class="bi bi-star{{ $contact->is_featured ? '-fill text-warning' : ' text-muted' }} star-btn flex-shrink-0"
                    data-id="{{ $contact->id }}" style="cursor: pointer; font-size: 1.25rem;"
                    title="{{ $contact->is_featured ? 'إزالة من المميزة' : 'إضافة للمميزة' }}"></i>
            </div>

            {{-- Info Section --}}
            <div class="contact-info-section bg-body-tertiary rounded-3 p-3 mb-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 36px; height: 36px;">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">رقم الهاتف</small>
                        <code class="contact-phone fs-6 fw-semibold" dir="ltr">{{ $contact->phone }}</code>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 36px; height: 36px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">آخر تواصل</small>
                        @if ($contact->last_sent_at)
                            <span class="text-success small fw-semibold">
                                <i
                                    class="bi bi-check2-circle me-1"></i>{{ $contact->last_sent_at->locale('ar')->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-secondary small">
                                <i class="bi bi-dash-circle me-1"></i>لم يتم التواصل
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions (Icon Buttons Row) --}}
            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                {{-- Call --}}
                <a href="tel:{{ $contact->phone }}"
                    class="btn btn-success rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 45px; height: 45px; font-size: 1.1rem;" title="اتصال">
                    <i class="bi bi-telephone-outbound-fill"></i>
                </a>

                {{-- Label --}}
                <button type="button"
                    class="btn btn-outline-secondary rounded-circle btn-label-mobile d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 45px; height: 45px; font-size: 1.1rem;" data-id="{{ $contact->id }}"
                    data-text="{{ $contact->label_text }}" data-color="{{ $contact->label_color }}"
                    data-bs-toggle="modal" data-bs-target="#labelModal" title="تسمية">
                    <i class="bi bi-tag-fill"></i>
                </button>

                {{-- Note --}}
                <button type="button"
                    class="btn btn-outline-info rounded-circle btn-note-mobile d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 45px; height: 45px; font-size: 1.1rem;" data-id="{{ $contact->id }}"
                    data-note="{{ $contact->notes }}" data-bs-toggle="modal" data-bs-target="#noteModal"
                    title="ملاحظات">
                    <i class="bi bi-journal-text"></i>
                </button>

                {{-- Edit --}}
                <button type="button"
                    class="btn btn-outline-primary rounded-circle edit-btn d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 45px; height: 45px; font-size: 1.1rem;" data-id="{{ $contact->id }}"
                    data-name="{{ $contact->name }}" data-phone="{{ $contact->phone }}" title="تعديل">
                    <i class="bi bi-pencil-square"></i>
                </button>

                {{-- Delete --}}
                <button type="button"
                    class="btn btn-outline-danger rounded-circle delete-btn d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 45px; height: 45px; font-size: 1.1rem;" data-id="{{ $contact->id }}"
                    data-name="{{ $contact->name }}" title="حذف">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </div>
        </div>
    </div>
@endforeach

@foreach ($contacts as $contact)
    <div class="contact-card card mb-3 shadow-sm border-0" id="mobile-contact-{{ $contact->id }}"
        data-id="{{ $contact->id }}" data-name="{{ strtolower($contact->name) }}"
        data-phone="{{ $contact->phone }}">
        <div class="card-body p-3">
            {{-- Header: Checkbox + Name + Star --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-width-0">
                    <input type="checkbox" class="form-check-input contact-checkbox flex-shrink-0"
                        value="{{ $contact->id }}" style="width: 1.25rem; height: 1.25rem;">
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="fw-bold mb-0 text-truncate contact-name">{{ $contact->name }}</h6>
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
                                <i class="bi bi-check2-circle me-1"></i>{{ $contact->last_sent_at->locale('ar')->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-secondary small">
                                <i class="bi bi-dash-circle me-1"></i>لم يتم التواصل
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            {{-- Call Client Button (Mobile Only - Full Width for easy tap) --}}
            <div class="d-grid mb-2">
                <a href="tel:{{ $contact->phone }}"
                   class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2">
                    <i class="bi bi-telephone-outbound-fill"></i>
                    <span class="fw-semibold">كلم العميل</span>
                </a>
            </div>
            {{-- Edit & Delete Buttons --}}
            <div class="d-flex gap-2">
                <button type="button"
                    class="btn btn-outline-primary flex-grow-1 edit-btn d-flex align-items-center justify-content-center gap-2"
                    data-id="{{ $contact->id }}" data-name="{{ $contact->name }}"
                    data-phone="{{ $contact->phone }}">
                    <i class="bi bi-pencil-square"></i>
                    <span>تعديل</span>
                </button>
                <button type="button"
                    class="btn btn-outline-danger flex-grow-1 delete-btn d-flex align-items-center justify-content-center gap-2"
                    data-id="{{ $contact->id }}" data-name="{{ $contact->name }}">
                    <i class="bi bi-trash3"></i>
                    <span>حذف</span>
                </button>
            </div>
        </div>
    </div>
@endforeach

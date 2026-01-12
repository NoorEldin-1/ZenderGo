@foreach ($contacts as $contact)
    <tr id="contact-row-{{ $contact->id }}" data-name="{{ strtolower($contact->name) }}"
        data-phone="{{ $contact->phone }}">
        <td class="ps-3">
            <input type="checkbox" class="form-check-input contact-checkbox" value="{{ $contact->id }}">
        </td>
        <td class="d-flex align-items-center">
            <i class="bi bi-star{{ $contact->is_featured ? '-fill text-warning' : ' text-muted' }} star-btn me-2"
                data-id="{{ $contact->id }}" style="cursor: pointer; font-size: 1.1rem; transition: transform 0.2s;"
                title="{{ $contact->is_featured ? 'إزالة من المميزة' : 'إضافة للمميزة' }}"></i>
            <div>
                <div class="fw-semibold contact-name">{{ $contact->name }}</div>
                @if (!empty($contact->share_history))
                    <small class="d-block mt-1">
                        <i class="bi bi-share-fill text-info me-1" style="font-size: 0.7rem;"></i>
                        @foreach ($contact->share_history as $share)
                            <span
                                class="badge {{ $share->status === 'accepted' ? 'bg-success' : ($share->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}"
                                style="font-size: 0.65rem;" dir="ltr"
                                title="{{ $share->status === 'accepted' ? 'مقبول' : ($share->status === 'pending' ? 'قيد الانتظار' : 'مرفوض') }}">
                                {{ $share->shared_with }}
                            </span>
                        @endforeach
                    </small>
                @endif
                <small class="text-muted d-md-none contact-phone" dir="ltr">{{ $contact->phone }}</small>
            </div>
        </td>
        <td class="d-none d-md-table-cell">
            <code class="contact-phone" dir="ltr">{{ $contact->phone }}</code>
        </td>
        <td class="d-none d-lg-table-cell">
            @if ($contact->last_sent_at)
                <span class="badge bg-success-subtle text-success">
                    <i class="bi bi-check2-circle me-1"></i>{{ $contact->last_sent_at->diffForHumans() }}
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary">
                    <i class="bi bi-dash-circle me-1"></i>لم يتم التواصل
                </span>
            @endif
        </td>
        <td class="text-muted small d-none d-xl-table-cell">
            {{ $contact->created_at->diffForHumans() }}
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary edit-btn" data-id="{{ $contact->id }}"
                    data-name="{{ $contact->name }}" data-phone="{{ $contact->phone }}" title="تعديل">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger delete-btn" data-id="{{ $contact->id }}"
                    data-name="{{ $contact->name }}" title="حذف">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach

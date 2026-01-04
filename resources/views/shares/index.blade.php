@extends('layouts.app')

@section('title', 'طلبات المشاركة')

@section('content')
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="mb-0 fw-bold">طلبات المشاركة</h2>
            @if ($pendingCount > 0)
                <span class="badge bg-danger">{{ $pendingCount }} جديد</span>
            @endif
        </div>
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>العودة لجهات الاتصال
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active position-relative" data-bs-toggle="tab" data-bs-target="#received">
                <i class="bi bi-inbox me-1"></i>الواردة
                @if ($pendingCount > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sent">
                <i class="bi bi-send me-1"></i>المرسلة
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Received Requests -->
        <div class="tab-pane fade show active" id="received">
            @if ($receivedRequests->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">لا توجد طلبات مشاركة واردة</p>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach ($receivedRequests as $request)
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-person-circle text-primary"></i>
                                                <strong dir="ltr">{{ $request->sender->phone }}</strong>
                                                @if ($request->isPending())
                                                    <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                                @elseif ($request->isAccepted())
                                                    <span class="badge bg-success">مقبول</span>
                                                @else
                                                    <span class="badge bg-secondary">مرفوض</span>
                                                @endif
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-people me-1"></i>
                                                {{ $request->contacts->count() }} جهة اتصال
                                            </p>
                                            @if ($request->message)
                                                <p class="mb-1 small fst-italic">
                                                    <i class="bi bi-chat-quote me-1"></i>"{{ $request->message }}"
                                                </p>
                                            @endif
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ $request->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        @if ($request->isPending())
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('shares.accept', $request->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-check-lg me-1"></i>قبول
                                                    </button>
                                                </form>
                                                <form action="{{ route('shares.reject', $request->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-x-lg me-1"></i>رفض
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Expandable contact list -->
                                    <div class="mt-2">
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#contacts-{{ $request->id }}">
                                            <i class="bi bi-eye me-1"></i>عرض جهات الاتصال
                                        </button>
                                        <div class="collapse mt-2" id="contacts-{{ $request->id }}">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>الاسم</th>
                                                            <th>الهاتف</th>
                                                            <th>المتجر</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($request->contacts as $contact)
                                                            <tr>
                                                                <td>{{ $contact->name }}</td>
                                                                <td dir="ltr">{{ $contact->phone }}</td>
                                                                <td>{{ $contact->store_name ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sent Requests -->
        <div class="tab-pane fade" id="sent">
            @if ($sentRequests->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-send text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">لم ترسل أي طلبات مشاركة بعد</p>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach ($sentRequests as $request)
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-person-circle text-info"></i>
                                                <strong dir="ltr">{{ $request->recipient->phone }}</strong>
                                                @if ($request->isPending())
                                                    <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                                @elseif ($request->isAccepted())
                                                    <span class="badge bg-success">مقبول</span>
                                                @else
                                                    <span class="badge bg-secondary">مرفوض</span>
                                                @endif
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-people me-1"></i>
                                                {{ $request->contacts->count() }} جهة اتصال
                                            </p>
                                            @if ($request->message)
                                                <p class="mb-1 small fst-italic">
                                                    <i class="bi bi-chat-quote me-1"></i>"{{ $request->message }}"
                                                </p>
                                            @endif
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ $request->created_at->diffForHumans() }}
                                                @if ($request->responded_at)
                                                    · تم الرد {{ $request->responded_at->diffForHumans() }}
                                                @endif
                                            </small>
                                        </div>
                                        @if ($request->isPending())
                                            <form action="{{ route('shares.destroy', $request->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-x-lg me-1"></i>إلغاء
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Expandable contact list -->
                                    <div class="mt-2">
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#sent-contacts-{{ $request->id }}">
                                            <i class="bi bi-eye me-1"></i>عرض جهات الاتصال
                                        </button>
                                        <div class="collapse mt-2" id="sent-contacts-{{ $request->id }}">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>الاسم</th>
                                                            <th>الهاتف</th>
                                                            <th>المتجر</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($request->contacts as $contact)
                                                            <tr>
                                                                <td>{{ $contact->name }}</td>
                                                                <td dir="ltr">{{ $contact->phone }}</td>
                                                                <td>{{ $contact->store_name ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('admin.layouts.app')

@section('title', 'طلبات الدفع')

@push('styles')
    <style>
        .stat-card {
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-credit-card text-success ms-2"></i>طلبات الدفع
        </h4>
    </div>

    {{-- Stats Grid: 4 Columns --}}
    <div class="row g-3 mb-4">
        {{-- Total Requests --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info text-white shadow-sm">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $totalCount }}</div>
                        <div class="stat-label text-muted">إجمالي الطلبات</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100 border-warning border-start border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning text-white shadow-sm">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $pendingCount }}</div>
                        <div class="stat-label text-muted">في الانتظار</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100 border-success border-start border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success text-white shadow-sm">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $approvedCount }}</div>
                        <div class="stat-label text-muted">مقبولة</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100 border-danger border-start border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger text-white shadow-sm">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $rejectedCount }}</div>
                        <div class="stat-label text-muted">مرفوضة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payment-requests.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="اسم المستخدم أو رقم الهاتف..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-whatsapp flex-fill">
                                <i class="bi bi-search me-1"></i>بحث
                            </button>
                            @if (request('search') || request('status'))
                                <a href="{{ route('admin.payment-requests.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Requests Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>رقم الهاتف</th>
                            <th>المبلغ</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-success"></i>
                                        </div>
                                        <span>{{ $request->user->name }}</span>
                                    </div>
                                </td>
                                <td dir="ltr" class="text-end">{{ $request->user->phone }}</td>
                                <td>{{ number_format($request->amount) }} ج</td>
                                <td>{{ $request->created_at->format('Y/m/d h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $request->status_badge_class }}">
                                        @if ($request->isPending())
                                            <i class="bi bi-hourglass-split me-1"></i>
                                        @elseif($request->isApproved())
                                            <i class="bi bi-check-circle me-1"></i>
                                        @else
                                            <i class="bi bi-x-circle me-1"></i>
                                        @endif
                                        {{ $request->status_text }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.payment-requests.show', $request) }}"
                                        class="btn btn-sm {{ $request->isPending() ? 'btn-warning' : 'btn-outline-success' }}"
                                        title="عرض">
                                        <i class="bi bi-eye"></i>
                                        @if ($request->isPending())
                                            مراجعة
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد طلبات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($paymentRequests->hasPages())
            <div class="card-footer d-flex justify-content-center py-3">
                {{ $paymentRequests->links() }}
            </div>
        @endif
    </div>
@endsection

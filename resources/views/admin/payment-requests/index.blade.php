@extends('layouts.app')

@section('title', 'طلبات الدفع')

@push('styles')
    <style>
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-box.pending {
            border-right: 4px solid #ffc107;
        }

        .stat-box .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-box .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
        }

        .stat-box .label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-credit-card text-success ms-2"></i>طلبات الدفع
        </h4>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right ms-1"></i>لوحة التحكم
            </a>
            <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                    <i class="bi bi-box-arrow-right ms-1"></i>خروج
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle ms-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pending Count --}}
    <div class="stats-row">
        <div class="stat-box pending">
            <div class="icon bg-warning bg-opacity-15 text-warning">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="value">{{ $pendingCount }}</div>
                <div class="label">طلبات في الانتظار</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form action="{{ route('admin.payment-requests.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">البحث</label>
                    <input type="text" name="search" class="form-control" placeholder="اسم المستخدم أو رقم الهاتف..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
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

    {{-- Requests Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
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
                            <tr class="{{ $request->isPending() ? 'table-warning bg-opacity-10' : '' }}">
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
                                <td>{{ $request->created_at->format('m/d H:i') }}</td>
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

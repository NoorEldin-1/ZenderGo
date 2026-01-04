@extends('layouts.app')

@section('title', 'لوحة الإدارة')

@push('styles')
    <style>
        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.15);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">
            <i class="bi bi-shield-lock text-success ms-2"></i>لوحة الإدارة
        </h4>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                <i class="bi bi-calendar3 ms-1"></i>
                {{ now()->format('Y/m/d') }}
            </span>
            <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                    <i class="bi bi-box-arrow-right ms-1"></i>خروج
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.users.index') }}" class="card text-decoration-none h-100"
                style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-25 p-3">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">إدارة المستخدمين</h6>
                        <small class="opacity-75">عرض وإدارة جميع المستخدمين</small>
                    </div>
                    <i class="bi bi-arrow-left-short fs-3 me-auto"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.payment-requests.index') }}" class="card text-decoration-none h-100 position-relative"
                style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-25 p-3">
                        <i class="bi bi-credit-card fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">طلبات الدفع</h6>
                        <small class="opacity-75">مراجعة وقبول طلبات الاشتراك</small>
                    </div>
                    <i class="bi bi-arrow-left-short fs-3 me-auto"></i>
                </div>
                @if (($stats['pending_payments_count'] ?? 0) > 0)
                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.9rem;">
                        {{ $stats['pending_payments_count'] }}
                        <span class="visually-hidden">طلبات معلقة</span>
                    </span>
                @endif
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.settings') }}" class="card text-decoration-none h-100"
                style="background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-25 p-3">
                        <i class="bi bi-sliders fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">إعدادات الاشتراكات</h6>
                        <small class="opacity-75">الفترة التجريبية وأسعار الاشتراك</small>
                    </div>
                    <i class="bi bi-arrow-left-short fs-3 me-auto"></i>
                </div>
            </a>
        </div>
    </div>


    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                        <i class="bi bi-people text-white"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['users_count']) }}</div>
                        <div class="stat-label">المستخدمين</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);">
                        <i class="bi bi-person-lines-fill text-white"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['contacts_count']) }}</div>
                        <div class="stat-label">جهات الاتصال</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                        <i class="bi bi-file-earmark-text text-white"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['templates_count']) }}</div>
                        <div class="stat-label">القوالب</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>أحدث المستخدمين
            </h6>
            <a href="{{ route('admin.users.index') }}" class="btn btn-whatsapp btn-sm">
                عرض الكل <i class="bi bi-arrow-left-short"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>رقم الهاتف</th>
                            <th>تاريخ التسجيل</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recent_users'] as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-success"></i>
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td dir="ltr" class="text-end">{{ $user->phone }}</td>
                                <td>{{ $user->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    لا يوجد مستخدمين بعد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

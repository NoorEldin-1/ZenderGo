@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="mb-4">
        <h2 class="mb-1 fw-bold">مرحباً! 👋</h2>
        <p class="text-muted mb-0">إليك ملخص لحسابك</p>
    </div>

    <div class="row g-3">
        <!-- Contacts Card -->
        <div class="col-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-2 p-md-3 rounded-3">
                                <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h3 class="mb-0 fw-bold">{{ Auth::user()->contacts()->count() }}</h3>
                            <p class="text-muted mb-0 small">جهات الاتصال</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-arrow-left me-1"></i>إدارة
                    </a>
                </div>
            </div>
        </div>

        <!-- Campaign Card -->
        <div class="col-6 col-lg-4">
            <div class="card h-100 border-success">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-2 p-md-3 rounded-3">
                                <i class="bi bi-megaphone text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h5 class="mb-0 fw-bold">حملة جديدة</h5>
                            <p class="text-muted mb-0 small">أرسل رسائل جماعية</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('campaigns.create') }}" class="btn btn-sm btn-whatsapp w-100">
                        <i class="bi bi-plus-lg me-1"></i>إنشاء
                    </a>
                </div>
            </div>
        </div>

        <!-- Import Card -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-2 p-md-3 rounded-3">
                                <i class="bi bi-file-earmark-excel text-info" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h5 class="mb-0 fw-bold">استيراد جهات</h5>
                            <p class="text-muted mb-0 small">من ملف Excel أو CSV</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-outline-info w-100">
                        <i class="bi bi-upload me-1"></i>استيراد الآن
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Getting Started -->
    <div class="card mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-rocket-takeoff text-primary me-2"></i>كيف تبدأ؟
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-5 p-2"
                                style="width: 40px; height: 40px; line-height: 24px;">1</span>
                        </div>
                        <div class="me-3">
                            <h6 class="fw-bold">أضف جهات الاتصال</h6>
                            <p class="text-muted small mb-0">استورد من Excel أو أضفهم يدوياً</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-5 p-2"
                                style="width: 40px; height: 40px; line-height: 24px;">2</span>
                        </div>
                        <div class="me-3">
                            <h6 class="fw-bold">أنشئ حملة</h6>
                            <p class="text-muted small mb-0">اكتب رسالتك واختر المستلمين</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-5 p-2"
                                style="width: 40px; height: 40px; line-height: 24px;">3</span>
                        </div>
                        <div class="me-3">
                            <h6 class="fw-bold">أرسل عبر واتساب</h6>
                            <p class="text-muted small mb-0">الرسائل تُرسل تلقائياً للجميع</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

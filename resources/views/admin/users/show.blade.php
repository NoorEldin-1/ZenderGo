@extends('layouts.app')

@section('title', 'تفاصيل المستخدم')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-person me-2 text-success"></i>تفاصيل المستخدم
        </h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>رجوع
        </a>
    </div>

    <div class="row g-4">
        <!-- User Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--whatsapp-green) 0%, var(--whatsapp-dark) 100%);">
                        <i class="bi bi-person fs-1 text-white"></i>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3" dir="ltr">{{ $user->phone }}</p>

                    @if ($user->email)
                        <p class="mb-2">
                            <i class="bi bi-envelope me-1"></i>
                            {{ $user->email }}
                        </p>
                    @endif

                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar3 me-1"></i>
                        مسجل منذ {{ $user->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>الإحصائيات</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">جهات الاتصال</span>
                        <span class="badge bg-info fs-6">{{ $user->contacts_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">القوالب</span>
                        <span class="badge bg-warning text-dark fs-6">{{ $user->templates_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">طلبات المشاركة المرسلة</span>
                        <span class="badge bg-primary fs-6">{{ $user->sent_share_requests_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">طلبات المشاركة المستلمة</span>
                        <span class="badge bg-success fs-6">{{ $user->received_share_requests_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Subscription Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gem me-2 text-info"></i>حالة الاشتراك</h6>
                </div>
                <div class="card-body">
                    @php
                        $subscription = $user->activeSubscription();
                    @endphp

                    {{-- If account is suspended for subscription reasons, show warning first --}}
                    @if ($user->is_suspended && $user->suspension_reason === 'subscription')
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                            <strong>الحساب معطل بسبب الاشتراك</strong>
                            <p class="mb-0 mt-1 small">تم تعطيل هذا الحساب لأسباب متعلقة بالاشتراك.</p>
                        </div>
                    @endif

                    @if ($subscription && !($user->is_suspended && $user->suspension_reason === 'subscription'))
                        @php
                            $timeInfo = $subscription->detailedTimeRemaining();
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">نوع الاشتراك</span>
                            @if ($subscription->isTrial())
                                <span class="badge bg-info fs-6">
                                    <i class="bi bi-gift me-1"></i>تجريبي
                                </span>
                            @else
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-patch-check me-1"></i>مدفوع
                                </span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">المدة المتبقية</span>
                            <div class="text-end">
                                <span class="badge bg-primary me-1">{{ $timeInfo['days'] }} يوم</span>
                                <span class="badge bg-info me-1">{{ $timeInfo['hours'] }} ساعة</span>
                                <span class="badge bg-warning text-dark">{{ $timeInfo['minutes'] }} دقيقة</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">ينتهي في</span>
                            <span class="text-dark fw-semibold">{{ $subscription->ends_at->format('Y/m/d - H:i') }}</span>
                        </div>
                        @if ($subscription->isPaid())
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">المبلغ المدفوع</span>
                                <span class="text-dark fw-semibold">{{ number_format($subscription->price_paid) }}
                                    جنيه</span>
                            </div>
                        @endif
                    @elseif (!$subscription || ($user->is_suspended && $user->suspension_reason === 'subscription'))
                        @if (!($user->is_suspended && $user->suspension_reason === 'subscription'))
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                لا يوجد اشتراك نشط
                            </div>
                        @endif
                    @endif

                    <hr class="my-3">

                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                        data-bs-target="#activateSubscriptionModal">
                        <i class="bi bi-credit-card me-1"></i>تفعيل اشتراك شهري
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>الإجراءات</h6>
                </div>
                <div class="card-body">
                    @if ($user->is_suspended)
                        <!-- Suspended State -->
                        <div class="alert alert-danger mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-pause-circle fs-4"></i>
                                <strong>الحساب معطل</strong>
                            </div>
                            <div class="small">
                                <strong>السبب:</strong>
                                {{ $user->suspension_reason === 'security' ? 'أمني' : 'اشتراك' }}
                            </div>
                            <div class="small text-muted">
                                <strong>تاريخ التعطيل:</strong>
                                {{ $user->suspended_at?->format('Y/m/d H:i') ?? 'غير محدد' }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-success w-100 mb-3" data-bs-toggle="modal"
                            data-bs-target="#unsuspendModal">
                            <i class="bi bi-play-circle me-1"></i>إعادة تفعيل الحساب
                        </button>
                    @else
                        <!-- Active State -->
                        <button type="button" class="btn btn-warning w-100 mb-3" data-bs-toggle="modal"
                            data-bs-target="#suspendModal">
                            <i class="bi bi-pause-circle me-1"></i>تعطيل الحساب
                        </button>
                    @endif

                    <hr class="my-3">

                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                        data-bs-target="#deleteUserModal">
                        <i class="bi bi-trash me-1"></i>حذف المستخدم نهائياً
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Contacts -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-person-lines-fill me-2"></i>أحدث جهات الاتصال
                    </h6>
                    <span class="badge bg-secondary">{{ $user->contacts_count }} إجمالي</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>رقم الهاتف</th>
                                    <th>المتجر</th>
                                    <th>تاريخ الإضافة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->contacts as $contact)
                                    <tr>
                                        <td>{{ $contact->name }}</td>
                                        <td dir="ltr" class="text-end">{{ $contact->phone }}</td>
                                        <td>{{ $contact->store_name ?? '-' }}</td>
                                        <td>{{ $contact->created_at->format('Y/m/d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            لا توجد جهات اتصال
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Session Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-whatsapp me-2 text-success"></i>معلومات WhatsApp</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Session</label>
                            <input type="text" class="form-control"
                                value="{{ $user->whatsapp_session ?? 'غير متاح' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">حالة الاتصال</label>
                            <input type="text" class="form-control"
                                value="{{ $user->whatsapp_session ? 'متصل' : 'غير متصل' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="suspendModalLabel">
                        <i class="bi bi-pause-circle me-2"></i>تعطيل الحساب
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-4">
                            سيتم تعطيل حساب <strong>{{ $user->name }}</strong> ولن يتمكن من تسجيل الدخول حتى يتم إعادة
                            تفعيله.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">سبب التعطيل <span class="text-danger">*</span></label>

                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="reason" id="reasonSecurity"
                                    value="security" required>
                                <label class="form-check-label" for="reasonSecurity">
                                    <strong class="d-block"><i class="bi bi-shield-exclamation text-danger me-1"></i>سبب
                                        أمني</strong>
                                    <small class="text-muted">سيظهر للمستخدم: "الحساب معطل لسبب أمني"</small>
                                </label>
                            </div>

                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input" type="radio" name="reason" id="reasonSubscription"
                                    value="subscription" required>
                                <label class="form-check-label" for="reasonSubscription">
                                    <strong class="d-block"><i
                                            class="bi bi-credit-card text-warning me-1"></i>اشتراك</strong>
                                    <small class="text-muted">سيظهر للمستخدم: "يرجى دفع الاشتراك لتستطيع التكملة"</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-pause-circle me-1"></i>تعطيل الحساب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activate Subscription Modal -->
    <div class="modal fade" id="activateSubscriptionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card me-2"></i>تفعيل الاشتراك
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-patch-check-fill text-success" style="font-size: 4rem;"></i>
                    <p class="mt-3 mb-0 fs-5">هل تريد تفعيل اشتراك شهري مدفوع لـ <strong>{{ $user->name }}</strong>؟</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form action="{{ route('admin.users.activate-subscription', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>نعم، تفعيل الاشتراك
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Unsuspend Modal -->
    <div class="modal fade" id="unsuspendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-play-circle me-2"></i>إعادة تفعيل الحساب
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-person-check-fill text-success" style="font-size: 4rem;"></i>
                    <p class="mt-3 mb-0 fs-5">هل تريد إعادة تفعيل حساب <strong>{{ $user->name }}</strong>؟</p>
                    <p class="text-muted small">سيتمكن المستخدم من تسجيل الدخول واستخدام النظام</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form action="{{ route('admin.users.unsuspend', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-circle me-1"></i>نعم، إعادة التفعيل
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-trash me-2"></i>حذف المستخدم
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                    <p class="mt-3 mb-0 fs-5">هل أنت متأكد من حذف <strong>{{ $user->name }}</strong>؟</p>
                    <p class="text-danger small">سيتم حذف جميع بياناته بما في ذلك جهات الاتصال والقوالب نهائياً!</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>نعم، حذف نهائياً
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

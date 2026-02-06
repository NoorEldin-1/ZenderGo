@extends('admin.layouts.app')

@section('title', 'المستخدمين')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-people me-2 text-success"></i>المستخدمين
        </h4>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success fs-6">
                {{ $users->total() }} مستخدم
            </span>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-speedometer2 me-1"></i>لوحة التحكم
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="البحث بالاسم أو رقم الهاتف أو البريد..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-whatsapp flex-fill">
                                <i class="bi bi-search me-1"></i>بحث
                            </button>
                            @if (request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>رقم الهاتف</th>
                            <th>الحالة</th>
                            <th>الاشتراك</th>
                            <th>جهات الاتصال</th>
                            <th>القوالب</th>
                            <th>تاريخ التسجيل</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="{{ $user->is_suspended ? 'table-danger bg-opacity-10' : '' }}">
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $user->is_suspended ? 'bg-danger' : 'bg-success' }} bg-opacity-10"
                                            style="width: 40px; height: 40px;">
                                            <i
                                                class="bi bi-person {{ $user->is_suspended ? 'text-danger' : 'text-success' }}"></i>
                                        </div>
                                        <div>
                                            <div>{{ $user->name }}</div>
                                            @if ($user->email)
                                                <small class="text-muted">{{ $user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td dir="ltr" class="text-end">{{ $user->phone }}</td>
                                <td>
                                    @if ($user->is_suspended)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-pause-circle me-1"></i>
                                            {{ $user->suspension_reason === 'security' ? 'معطل (أمني)' : 'معطل (اشتراك)' }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>نشط
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $subscription = $user->activeSubscription();
                                    @endphp
                                    @if ($subscription)
                                        @php
                                            $timeInfo = $subscription->detailedTimeRemaining();
                                        @endphp
                                        @if ($subscription->isTrial())
                                            <span class="badge bg-info" title="{{ $timeInfo['formatted'] }}">
                                                <i class="bi bi-gift me-1"></i>تجريبي ({{ $timeInfo['formatted'] }})
                                            </span>
                                        @else
                                            <span class="badge bg-success" title="{{ $timeInfo['formatted'] }}">
                                                <i class="bi bi-patch-check me-1"></i>مدفوع
                                                ({{ $timeInfo['formatted'] }})
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>منتهي
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $user->contacts_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $user->templates_count }}</span>
                                </td>
                                <td>{{ $user->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="btn btn-sm btn-outline-success" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user"
                                            title="حذف" data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}" data-user-phone="{{ $user->phone }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا يوجد مستخدمين
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
            <div class="card-footer d-flex justify-content-center py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger" id="deleteUserModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-person-x text-danger" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h5 class="mb-2">هل أنت متأكد من حذف هذا المستخدم؟</h5>
                    <p class="text-muted mb-3" id="deleteUserInfo"></p>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <div>
                            سيتم حذف جميع بيانات المستخدم بما في ذلك جهات الاتصال والقوالب والحملات.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>إلغاء
                    </button>
                    <form id="deleteUserForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash me-1"></i>حذف المستخدم
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            const deleteForm = document.getElementById('deleteUserForm');
            const deleteUserInfo = document.getElementById('deleteUserInfo');

            document.querySelectorAll('.btn-delete-user').forEach(function(button) {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const userName = this.dataset.userName;
                    const userPhone = this.dataset.userPhone;

                    deleteUserInfo.innerHTML = '<strong>' + userName +
                        '</strong><br><span dir="ltr">' + userPhone + '</span>';
                    deleteForm.action = '{{ route('admin.users.index') }}/' + userId;

                    deleteModal.show();
                });
            });
        });
    </script>
@endpush

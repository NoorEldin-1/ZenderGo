@extends('layouts.app')

@section('title', 'تحديد أعمدة الملف')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-table text-primary me-2"></i>تحديد أعمدة الملف
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        يمكنك كتابة اسم العمود أو اختياره من القائمة.
                    </div>

                    <div class="d-flex align-items-center mb-4 text-muted small">
                        <i class="bi bi-file-earmark-excel fs-5 me-2"></i>
                        <strong>الملف:</strong> <span class="ms-1">{{ $filename }}</span>
                    </div>

                    <form action="{{ route('contacts.process-mapping') }}" method="POST">
                        @csrf

                        <!-- Datalist for suggestions -->
                        <datalist id="headersList">
                            @foreach ($headers as $index => $header)
                                <option value="{{ $header ?: 'عمود ' . ($index + 1) }}">
                            @endforeach
                        </datalist>

                        <div class="row g-4">
                            <!-- Name Field -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">الاسم (مطلوب)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input class="form-control @error('name_column') is-invalid @enderror"
                                        list="headersList" name="name_column" placeholder="اكتب أو اختر اسم عمود الاسم..."
                                        value="{{ old('name_column', $suggested_name !== '' ? ($headers[$suggested_name] ?: 'عمود ' . ($suggested_name + 1)) : '') }}"
                                        required>
                                </div>
                                @error('name_column')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Field -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">رقم الهاتف (مطلوب)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                                    <input class="form-control @error('phone_column') is-invalid @enderror"
                                        list="headersList" name="phone_column" placeholder="اكتب أو اختر اسم عمود الهاتف..."
                                        value="{{ old('phone_column', $suggested_phone !== '' ? ($headers[$suggested_phone] ?: 'عمود ' . ($suggested_phone + 1)) : '') }}"
                                        required>
                                </div>
                                @error('phone_column')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Store Field Removed -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                            <a href="{{ route('contacts.index') }}" class="btn btn-link text-muted text-decoration-none">
                                إلغاء الأمر
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                معاينة البيانات <i class="bi bi-arrow-left ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

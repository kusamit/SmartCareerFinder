@extends('layouts.app')
@section('title', 'Register')

@push('styles')
<style>
    /* Page background override */
    body { background: #f1f4f9 !important; }

    /* Kill autofill tint everywhere on this page */
    .register-page input:-webkit-autofill,
    .register-page input:-webkit-autofill:hover,
    .register-page input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;
        -webkit-text-fill-color: #0f172a !important;
        border-color: #e2e8f0 !important;
    }

    /* Kill the dark card border inherited from Tailwind/app.css */
    .register-card * { box-sizing: border-box; }

    .register-page {
        min-height: calc(100vh - 64px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
    }

    .register-card {
        background: #ffffff !important;
        border: 1.5px solid #e8edf5 !important;
        border-radius: 24px !important;
        padding: 44px 44px !important;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.07) !important;
        width: 100%;
        max-width: 680px;
    }

    .reg-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .reg-title {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
        margin: 0 0 6px 0;
    }

    .reg-subtitle {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }

    /* Role toggle */
    .role-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 24px;
    }

    .role-option {
        cursor: pointer;
        display: block;
    }

    .role-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .role-btn {
        display: block;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px 12px;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .role-btn:hover {
        border-color: #c7d2fe;
        background: #f5f3ff;
    }

    .role-btn.selected {
        border-color: #4f46e5;
        background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .role-btn-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        display: block;
    }

    .role-btn.selected .role-btn-title {
        color: #4f46e5;
    }

    /* Black color for subtext as requested */
    .role-btn-sub {
        font-size: 11.5px;
        font-weight: 700;
        color: #0f172a !important;
        display: block;
        margin-top: 3px;
    }

    /* Error box */
    .reg-error {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        color: #b91c1c;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 20px;
    }

    /* Form grid: 2 fields in one row */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 20px;
    }

    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .register-card {
            padding: 32px 22px !important;
        }
    }

    .field {
        margin-bottom: 4px;
    }

    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
    }

    .field-input {
        display: block;
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .field-input:focus {
        border-color: #4f46e5;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .field-input::placeholder {
        color: #94a3b8;
    }

    /* Submit button */
    .reg-submit {
        display: block;
        width: 100%;
        padding: 14px;
        margin-top: 24px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.01em;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.28);
    }

    .reg-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(79, 70, 229, 0.38);
    }

    /* Footer text */
    .reg-footer {
        text-align: center;
        margin-top: 22px;
        font-size: 13.5px;
        color: #64748b;
    }

    .reg-footer a {
        color: #4f46e5;
        font-weight: 700;
        text-decoration: none;
        margin-left: 4px;
    }

    .reg-footer a:hover {
        text-decoration: underline;
    }

    /* Divider */
    .reg-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 0 0 22px 0;
    }
</style>
@endpush

@section('content')
<div class="register-page">
    <div class="register-card">

        <div class="reg-header">
            <h1 class="reg-title">Create Account</h1>
            <p class="reg-subtitle">Join Smart CareerFinder and land your perfect role</p>
        </div>

        <div class="reg-divider"></div>

        @if ($errors->any())
        <div class="reg-error">
            @foreach($errors->all() as $e)
            <div>• {{ $e }}</div>
            @endforeach
        </div>
        @endif

        {{-- Role Selector --}}
        <div style="margin-bottom: 22px;">
            <span class="field-label">I want to...</span>
            <div class="role-row" id="roleSelector">
                <label class="role-option" for="role_seeker">
                    <input type="radio" id="role_seeker" name="role_display" value="seeker" checked>
                    <div class="role-btn selected" id="box_seeker">
                        <span class="role-btn-title">Find a Job</span>
                        <span class="role-btn-sub">Job Seeker</span>
                    </div>
                </label>
                <label class="role-option" for="role_provider">
                    <input type="radio" id="role_provider" name="role_display" value="provider">
                    <div class="role-btn" id="box_provider">
                        <span class="role-btn-title">Hire Talent</span>
                        <span class="role-btn-sub">Job Provider</span>
                    </div>
                </label>
            </div>
        </div>

        <form method="POST" action="{{ route('register.post') }}" id="registerForm">
            @csrf
            <input type="hidden" name="role" id="roleInput" value="seeker">

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="field-input" placeholder="" required>
                </div>

                <div class="field">
                    <label class="field-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="" required>
                </div>

                <div class="field" id="companyField" style="display: none; grid-column: 1 / -1;">
                    <label class="field-label" for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" class="field-input" placeholder="">
                </div>

                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="field-input" placeholder="" required>
                </div>

                <div class="field">
                    <label class="field-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="field-input" placeholder="" required>
                </div>
            </div>

            <button type="submit" class="reg-submit">Create Account</button>
        </form>

        <p class="reg-footer">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </p>

    </div>
</div>

@push('scripts')
<script>
    const roleInput   = document.getElementById('roleInput');
    const companyField = document.getElementById('companyField');
    const boxSeeker   = document.getElementById('box_seeker');
    const boxProvider = document.getElementById('box_provider');

    document.querySelectorAll('input[name="role_display"]').forEach(radio => {
        radio.addEventListener('change', function () {
            roleInput.value = this.value;
            companyField.style.display = (this.value === 'provider') ? 'block' : 'none';

            if (this.value === 'seeker') {
                boxSeeker.classList.add('selected');
                boxProvider.classList.remove('selected');
            } else {
                boxProvider.classList.add('selected');
                boxSeeker.classList.remove('selected');
            }
        });
    });
</script>
@endpush
@endsection

@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-8">
    <div class="w-full max-w-lg fade-up">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">J</div>
            <h1 class="text-3xl font-bold tracking-tight">Create Account</h1>
            <p class="text-slate-400 mt-1 text-sm">Join Career Finder and find your perfect match</p>
        </div>

        <div class="card p-8 card-glow">
            @if ($errors->any())
            <div class="bg-red-900/40 border border-red-600/40 text-red-300 px-4 py-3 rounded-xl text-sm mb-6">
                @foreach($errors->all() as $e)
                <div>• {{ $e }}</div>
                @endforeach
            </div>
            @endif

            {{-- Role Toggle --}}
            <div class="mb-6">
                <label class="label">I am a...</label>
                <div class="grid grid-cols-2 gap-3" id="roleSelector">
                    <label class="role-card cursor-pointer" for="role_seeker">
                        <input type="radio" id="role_seeker" name="role_display" value="seeker" class="sr-only" checked>
                        <div class="role-box border-2 border-indigo-500 bg-indigo-500/10 rounded-xl p-4 text-center transition-all">
                            <div class="font-semibold text-sm">Job Seeker</div>
                            <div class="text-slate-400 text-xs mt-0.5">Looking for work</div>
                        </div>
                    </label>
                    <label class="role-card cursor-pointer" for="role_provider">
                        <input type="radio" id="role_provider" name="role_display" value="provider" class="sr-only">
                        <div class="role-box border-2 border-slate-700 bg-slate-800/30 rounded-xl p-4 text-center transition-all">
                            <div class="font-semibold text-sm">Job Provider</div>
                            <div class="text-slate-400 text-xs mt-0.5">Hiring talent</div>
                        </div>
                    </label>
                </div>
            </div>

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4" id="registerForm">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="seeker">

                <div>
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" placeholder="Your full name" required>
                </div>

                {{-- Provider extra field --}}
                <div id="companyField" class="hidden">
                    <label class="label">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="input" placeholder="Your company name">
                </div>

                <div>
                    <label class="label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="you@example.com" required>
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" placeholder="Min 6 characters" required>
                </div>
                <div>
                    <label class="label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="input" placeholder="Repeat password" required>
                </div>

                <button type="submit" class="btn-primary w-full justify-center flex mt-2">Create Account</button>
            </form>

            <p class="text-center text-slate-400 text-sm mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Sign in</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const radios = document.querySelectorAll('input[name="role_display"]');
    const roleInput = document.getElementById('roleInput');
    const companyField = document.getElementById('companyField');

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            roleInput.value = this.value;
            companyField.classList.toggle('hidden', this.value !== 'provider');

            // Update card styles
            document.querySelectorAll('.role-card').forEach(card => {
                const box = card.querySelector('.role-box');
                const isSelected = card.querySelector('input').value === this.value;
                box.classList.toggle('border-indigo-500', isSelected);
                box.classList.toggle('bg-indigo-500/10', isSelected);
                box.classList.toggle('border-slate-700', !isSelected);
                box.classList.toggle('bg-slate-800/30', !isSelected);
            });
        });
    });
</script>
@endpush
@endsection


<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SeekerController;
use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public job listing
Route::get('/jobs', [JobController::class, 'publicList'])->name('jobs.public');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

// Job Seeker routes
Route::middleware('auth.seeker')->prefix('seeker')->name('seeker.')->group(function () {
    Route::get('/dashboard', [SeekerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [SeekerController::class, 'profile'])->name('profile');
    Route::get('/profile/view', [SeekerController::class, 'profileView'])->name('profile.view');
    Route::post('/profile', [SeekerController::class, 'updateProfile'])->name('profile.update');
    Route::get('/jobs', [SeekerController::class, 'jobs'])->name('jobs');
    Route::post('/jobs/find-by-cv', [SeekerController::class, 'findByCv'])->name('jobs.cv');
    Route::post('/jobs/{job}/apply', [SeekerController::class, 'apply'])->name('jobs.apply');
    Route::get('/applications', [SeekerController::class, 'applications'])->name('applications');
    Route::get('/education', [SeekerController::class, 'educationIndex'])->name('education');
    Route::post('/education', [SeekerController::class, 'educationStore'])->name('education.store');
    Route::put('/education/{education}', [SeekerController::class, 'educationUpdate'])->name('education.update');
    Route::delete('/education/{education}', [SeekerController::class, 'educationDestroy'])->name('education.destroy');
});

// Job Provider routes
Route::middleware('auth.provider')->prefix('provider')->name('provider.')->group(function () {
    Route::get('/dashboard', [ProviderController::class, 'dashboard'])->name('dashboard');
    Route::get('/jobs', [ProviderController::class, 'jobs'])->name('jobs');
    Route::get('/jobs/create', [ProviderController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [ProviderController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit', [ProviderController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [ProviderController::class, 'update'])->name('jobs.update');
    Route::patch('/jobs/{job}/status', [ProviderController::class, 'toggleStatus'])->name('jobs.status');
    Route::delete('/jobs/{job}', [ProviderController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/jobs/{job}/applicants', [ProviderController::class, 'applicants'])->name('jobs.applicants');
    Route::get('/applicants', [ProviderController::class, 'allApplicants'])->name('all_applicants');
    Route::patch('/applications/{application}/status', [ProviderController::class, 'updateApplicationStatus'])->name('applications.status');
});

Smart Career Finder
A Job Portal
A Laravel-based job portal with role-based access, CV upload, and intelligent job matching.

## Features

### 🔍 Job Seeker
- Register & login
- Update profile (skills, education, experience, location)
- Auto-generated natural language profile summary
- **Smart job matching** based on profile (keyword scoring: skills + experience + location)
- **Upload CV** (PDF/DOC/TXT) and get jobs matched to CV content
- Apply to jobs with one click
- Track all applications and their status

### 🏢 Job Provider
- Register & login
- Post, edit, delete job listings
- Toggle job status: **Open / Closed**
- View all applicants per job (sorted by match score)
- Dashboard with stats

---

## Tech Stack

- **Backend:** Laravel 10+
- **Frontend:** Blade Templates + Tailwind CSS (CDN) + Vanilla JS
- **Database:** MySQL / SQLite
- **Session-based auth** (no Sanctum/Breeze needed)

---

## Setup Instructions

### 1. Create a new Laravel project
```bash
composer create-project laravel/laravel job-portal
cd job-portal
```

### 2. Copy the files
Copy all files from this package into the corresponding directories of your Laravel project.

### 3. Configure .env
```env
APP_NAME=JobBridge
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jobportal
DB_USERNAME=root
DB_PASSWORD=

# For file uploads (CV storage)
FILESYSTEM_DISK=public
```

### 4. Register Middleware Aliases
In `bootstrap/app.php` (Laravel 11+):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth.seeker'   => \App\Http\Middleware\AuthSeeker::class,
        'auth.provider' => \App\Http\Middleware\AuthProvider::class,
    ]);
})
```

Or in `app/Http/Kernel.php` (Laravel 10):
```php
protected $middlewareAliases = [
    'auth.seeker'   => \App\Http\Middleware\AuthSeeker::class,
    'auth.provider' => \App\Http\Middleware\AuthProvider::class,
];
```

### 5. Run migrations & seed
```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 6. Start the server
```bash
php artisan serve
```

Visit: http://localhost:8000

---

## Demo Accounts

| Role          | Email               | Password   |
|---------------|---------------------|------------|
| Job Seeker    | seeker@demo.com     | password   |
| Job Provider  | provider@demo.com   | password   |

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php       # Login, Register, Logout
│   │   ├── SeekerController.php     # Dashboard, Profile, Jobs, CV Upload, Apply
│   │   ├── ProviderController.php   # Dashboard, CRUD Jobs, Applicants
│   │   └── JobController.php        # Public job listing
│   └── Middleware/
│       ├── AuthSeeker.php
│       └── AuthProvider.php
├── Models/
│   ├── User.php                     # With matchScore() method
│   ├── Job.php
│   └── Application.php
database/
├── migrations/
│   ├── create_users_table.php
│   └── create_jobs_table.php
└── seeders/
    └── DatabaseSeeder.php
resources/views/
├── layouts/app.blade.php            # Main layout
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── seeker/
│   ├── dashboard.blade.php
│   ├── profile.blade.php
│   ├── jobs.blade.php               # Matching + CV Upload
│   └── applications.blade.php
└── provider/
    ├── dashboard.blade.php
    ├── jobs.blade.php
    ├── job-form.blade.php           # Create/Edit
    └── applicants.blade.php
routes/web.php
```

---

## How Job Matching Works (from diagram)

1. **Profile → Text**: User profile is converted to natural language summary
2. **Preprocessing**: Text is lowercased, normalized
3. **Matching**: Keywords from profile/CV are compared against job's key_skills + description
4. **Scoring**:
   - Skill overlap (up to 60 pts)
   - Experience match (up to 20 pts)
   - Location match (up to 20 pts)
5. **Ranking**: Jobs sorted by match score (0–100%)
6. **CV Mode**: Upload CV → extract text → re-run matching

This is a simplified but effective version of the FAISS-based semantic matching shown in the diagram.

---

## Extending

- **Real FAISS/embeddings**: Replace `matchScore()` in `User.php` with an API call to a Python FastAPI service using Sentence-BERT
- **PDF text extraction**: Use `smalot/pdfparser` package for proper CV parsing
- **Email notifications**: Add `Notification` classes for application updates
- **Admin panel**: Add a third role `admin` with full control

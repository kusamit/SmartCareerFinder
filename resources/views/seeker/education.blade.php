@extends('layouts.app')
@section('title', 'Manage Education')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seeker-education.css') }}">
@endpush

@section('content')
<div class="edu-page-container fade-up">
    
    {{-- Header --}}
    <div class="edu-header">
        <div class="header-left-group">
            <h1 class="header-title">Education</h1>
        </div>
        <div class="header-actions">
            <button class="header-action-btn" title="Reorder">⇳</button>
            <button class="header-action-btn plus-btn" onclick="openAddModal()">+</button>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="edu-alert">
        <span style="font-size: 16px;">✓</span>
        {{ session('success') }}
    </div>
    @endif

    {{-- List --}}
    @forelse($educations as $edu)
        @php
            $schoolLetter = strtoupper(substr($edu->school, 0, 1));
        @endphp
        <div class="edu-card">
            <div class="edu-card-left">
                <div class="school-logo-placeholder">
                    {{ $schoolLetter }}
                </div>
                <div class="edu-details">
                    <div class="school-name">{{ $edu->school }}</div>
                    <div class="degree-info">{{ $edu->degree }}, {{ $edu->field_of_study }}</div>
                    <div class="attendance-dates">{{ $edu->start_year }} – {{ $edu->end_year }}</div>
                </div>
            </div>
            
            <button class="edit-btn" onclick="openEditModal({{ json_encode($edu) }})" title="Edit Education">✎</button>
        </div>
    @empty
        <div class="edu-empty-state">
            <div class="edu-empty-title">No education details added yet</div>
            <div class="edu-empty-sub">Add your degrees and study background to increase match relevancy</div>
            <button class="btn-save" onclick="openAddModal()">+ Add Education</button>
        </div>
    @endforelse

</div>

{{-- Add Modal --}}
<div class="edu-modal" id="addModal">
    <div class="modal-content-container">
        <div class="modal-header">
            <h2 class="modal-title">Add Education</h2>
            <button class="close-modal-btn" onclick="closeAddModal()">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('seeker.education.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">School / Institution</label>
                <input type="text" name="school" required class="form-input" placeholder="e.g. Tribhuvan University">
            </div>

            <div class="form-group">
                <label class="form-label">Degree</label>
                <input type="text" name="degree" required class="form-input" placeholder="e.g. Bachelor's degree">
            </div>

            <div class="form-group">
                <label class="form-label">Field of Study</label>
                <input type="text" name="field_of_study" required class="form-input" placeholder="e.g. BCA">
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Start Year</label>
                    <input type="text" name="start_year" required class="form-input" placeholder="e.g. 2021">
                </div>
                <div class="form-group">
                    <label class="form-label">End Year (or Expected)</label>
                    <input type="text" name="end_year" required class="form-input" placeholder="e.g. 2026">
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="edu-modal" id="editModal">
    <div class="modal-content-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Education</h2>
            <button class="close-modal-btn" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">School / Institution</label>
                <input type="text" name="school" id="edit_school" required class="form-input" placeholder="e.g. Tribhuvan University">
            </div>

            <div class="form-group">
                <label class="form-label">Degree</label>
                <input type="text" name="degree" id="edit_degree" required class="form-input" placeholder="e.g. Bachelor's degree">
            </div>

            <div class="form-group">
                <label class="form-label">Field of Study</label>
                <input type="text" name="field_of_study" id="edit_field_of_study" required class="form-input" placeholder="e.g. BCA">
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Start Year</label>
                    <input type="text" name="start_year" id="edit_start_year" required class="form-input" placeholder="e.g. 2021">
                </div>
                <div class="form-group">
                    <label class="form-label">End Year (or Expected)</label>
                    <input type="text" name="end_year" id="edit_end_year" required class="form-input" placeholder="e.g. 2026">
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn-delete" id="deleteBtn" onclick="deleteEducation()">Delete</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Update</button>
            </div>
        </form>

        <form method="POST" id="deleteForm" action="" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const deleteForm = document.getElementById('deleteForm');

    function openAddModal() {
        addModal.classList.add('active');
    }
    function closeAddModal() {
        addModal.classList.remove('active');
    }

    function openEditModal(edu) {
        document.getElementById('edit_school').value = edu.school;
        document.getElementById('edit_degree').value = edu.degree;
        document.getElementById('edit_field_of_study').value = edu.field_of_study;
        document.getElementById('edit_start_year').value = edu.start_year;
        document.getElementById('edit_end_year').value = edu.end_year;

        // Set action URIs
        editForm.action = `/seeker/education/${edu.id}`;
        deleteForm.action = `/seeker/education/${edu.id}`;

        editModal.classList.add('active');
    }
    function closeEditModal() {
        editModal.classList.remove('active');
    }

    function deleteEducation() {
        if (confirm('Are you sure you want to remove this education detail?')) {
            deleteForm.submit();
        }
    }

    // Close modals on clicking backdrop
    window.addEventListener('click', (e) => {
        if (e.target === addModal) closeAddModal();
        if (e.target === editModal) closeEditModal();
    });
</script>
@endsection

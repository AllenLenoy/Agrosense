@extends('layouts.app')
@section('title', 'My Profile')
@section('breadcrumb', 'Account / Profile')

@section('content')
<style>
.pro-header{margin-bottom:1.5rem}
.pro-grid{display:grid;grid-template-columns:1fr 2fr;gap:1.5rem}
.pro-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem}
.pro-card-title{font-size:1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
.pro-card-desc{font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem}
.pro-avatar-sec{text-align:center;padding:1.5rem 0;border-bottom:1px solid var(--border-color);margin-bottom:1.5rem}
.pro-avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--bg-body);box-shadow:0 8px 16px rgba(0,0,0,.05);margin:0 auto 1rem}
.pro-avatar-btn{background:var(--bg-body);border:1px solid var(--border-color);padding:.5rem 1rem;border-radius:8px;font-size:.8rem;font-weight:600;color:var(--text-main);cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s}
.pro-avatar-btn:hover{border-color:var(--primary);color:var(--primary)}
.pro-info{display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;font-size:.85rem;color:var(--text-muted)}
.pro-info i{width:16px;color:#94a3b8;text-align:center}
.pro-label{display:block;font-size:.75rem;font-weight:700;color:var(--text-main);margin-bottom:.35rem}
.pro-input{width:100%;padding:.75rem 1rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;transition:all .2s}
.pro-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.pro-textarea{width:100%;padding:.75rem 1rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;resize:vertical;min-height:100px;transition:all .2s}
.pro-textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.pro-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.pro-btn{padding:.75rem 1.5rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;transition:all .2s}
.pro-btn:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 4px 12px rgba(33,150,83,.2)}
.pro-alert{background:#dcfce7;border:1px solid #22c55e;color:#166534;padding:1rem;border-radius:8px;margin-bottom:1.5rem;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:.5rem}
.pro-error{color:#ef4444;font-size:.75rem;margin-top:.25rem}
@media(max-width:768px){.pro-grid{grid-template-columns:1fr}.pro-row{grid-template-columns:1fr}}
</style>

<div class="pro-header">
    <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">My Profile</h1>
    <p style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem">Manage your account settings and preferences.</p>
</div>

@if(session('success'))
<div class="pro-alert"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="pro-grid">
    <!-- Left Column: Avatar & Quick Info -->
    <div>
        <div class="pro-card">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                @csrf @method('PUT')
                <div class="pro-avatar-sec">
                    <img src="{{ $user->avatar_url }}" class="pro-avatar" id="avatarPreview">
                    <label class="pro-avatar-btn">
                        <i class="fas fa-camera"></i> Change Photo
                        <input type="file" name="avatar" style="display:none" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                    </label>
                    @error('avatar')<div class="pro-error">{{ $message }}</div>@enderror
                </div>
            </form>

            <div class="pro-info"><i class="fas fa-envelope"></i> {{ $user->email }}</div>
            <div class="pro-info"><i class="fas fa-shield-alt"></i> {{ ucfirst($user->role) }} Account</div>
            <div class="pro-info"><i class="fas fa-calendar-alt"></i> Joined {{ $user->created_at->format('F Y') }}</div>
            @if($user->location)<div class="pro-info"><i class="fas fa-map-marker-alt"></i> {{ $user->location }}</div>@endif
        </div>
    </div>

    <!-- Right Column: Forms -->
    <div>
        <div class="pro-card">
            <h3 class="pro-card-title"><i class="fas fa-user-edit" style="color:var(--primary)"></i> Personal Information</h3>
            <p class="pro-card-desc">Update your personal details and public profile information.</p>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf @method('PUT')
                
                <div class="pro-row">
                    <div>
                        <label class="pro-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="pro-input" required>
                        @error('name')<div class="pro-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="pro-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="pro-input" required>
                        @error('email')<div class="pro-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="pro-row">
                    <div>
                        <label class="pro-label">Phone Number (Optional)</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="pro-input">
                        @error('phone')<div class="pro-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="pro-label">Location (Optional)</label>
                        <input type="text" name="location" value="{{ old('location', $user->location) }}" class="pro-input">
                        @error('location')<div class="pro-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label class="pro-label">Bio (Optional)</label>
                    <textarea name="bio" class="pro-textarea" placeholder="Write a short bio about yourself and your farming experience...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<div class="pro-error">{{ $message }}</div>@enderror
                </div>

                <div style="text-align:right">
                    <button type="submit" class="pro-btn">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="pro-card">
            <h3 class="pro-card-title"><i class="fas fa-lock" style="color:var(--primary)"></i> Security</h3>
            <p class="pro-card-desc">Ensure your account is using a long, random password to stay secure.</p>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf @method('PUT')
                
                <div style="margin-bottom:1rem">
                    <label class="pro-label">Current Password</label>
                    <input type="password" name="current_password" class="pro-input" required autocomplete="current-password">
                    @error('current_password')<div class="pro-error">{{ $message }}</div>@enderror
                </div>

                <div class="pro-row">
                    <div>
                        <label class="pro-label">New Password</label>
                        <input type="password" name="password" class="pro-input" required autocomplete="new-password">
                        @error('password')<div class="pro-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="pro-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="pro-input" required autocomplete="new-password">
                    </div>
                </div>

                <div style="text-align:right">
                    <button type="submit" class="pro-btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

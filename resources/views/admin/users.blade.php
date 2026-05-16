@extends('layouts.app')
@section('title', 'Manage Users')
@section('breadcrumb', 'Administration / Users')

@section('content')
<style>
    .admin-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
    .admin-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0; }
    .admin-subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem; }
    .admin-tabs { display: flex; gap: 0.5rem; }
    .admin-tab {
        padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; color: var(--text-muted); background: var(--bg-body);
        border: 1px solid var(--border-color); transition: all 0.2s;
    }
    .admin-tab:hover { border-color: var(--primary); color: var(--primary); }
    .admin-tab.active { background: var(--primary); color: white; border-color: var(--primary); }

    /* User Stats Bar */
    .user-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
    .user-stat-pill {
        display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem;
        background: #fff; border: 1px solid var(--border-color); border-radius: 10px;
        font-size: 0.85rem; font-weight: 600; color: var(--text-main);
    }
    .user-stat-pill .count { font-weight: 800; font-size: 1.1rem; }
    .user-stat-pill.highlight { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }

    /* Toolbar */
    .user-toolbar {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
    }
    .user-search {
        display: flex; align-items: center; gap: 0.5rem; background: #fff;
        border: 1px solid var(--border-color); border-radius: 8px; padding: 0.5rem 1rem;
        transition: all 0.2s; width: 320px;
    }
    .user-search:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33,150,83,0.1); }
    .user-search input { background: transparent; border: none; outline: none; width: 100%; font-size: 0.9rem; color: var(--text-main); }
    .user-filter-btn {
        padding: 0.5rem 1rem; background: #fff; border: 1px solid var(--border-color);
        border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);
        cursor: pointer; transition: all 0.2s; text-decoration: none;
    }
    .user-filter-btn:hover, .user-filter-btn.active { border-color: var(--primary); color: var(--primary); }

    /* User Table */
    .user-table-wrap {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        overflow: hidden;
    }
    .user-table { width: 100%; border-collapse: collapse; }
    .user-table th {
        padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);
        background: #fafbfc; border-bottom: 1px solid var(--border-color);
    }
    .user-table td {
        padding: 0.85rem 1.25rem; font-size: 0.85rem; color: var(--text-main);
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .user-table tr:last-child td { border-bottom: none; }
    .user-table tr:hover td { background: #fafbfc; }
    .user-table-avatar {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
        border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .user-name-cell { display: flex; align-items: center; gap: 0.75rem; }
    .user-name-text { font-weight: 700; }
    .user-badge {
        font-size: 0.6rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .user-badge.admin { background: linear-gradient(135deg, #fee2e2, #fecdd3); color: #be123c; }
    .user-badge.farmer { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 0.35rem; }
    .status-dot.active { background: #22c55e; }
    .status-dot.inactive { background: #ef4444; }
    .action-menu { display: flex; gap: 0.5rem; }
    .action-menu .act-btn {
        width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border-color);
        background: #fff; display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.75rem; color: var(--text-muted); transition: all 0.2s;
    }
    .action-menu .act-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
    .action-menu .act-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: #fee2e2; }

    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); }
    @media (max-width: 768px) { .admin-header { flex-direction: column; gap: 1rem; } .user-stats { flex-wrap: wrap; } .user-toolbar { flex-direction: column; align-items: stretch; } .user-search { width: 100%; } }
</style>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">User Management</h1>
        <p class="admin-subtitle">Manage platform users, roles, and permissions</p>
    </div>
    <div class="admin-tabs">
        <a href="{{ route('admin.index') }}" class="admin-tab">
            <i class="fas fa-th-large"></i> Overview
        </a>
        <a href="{{ route('admin.users') }}" class="admin-tab active">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="{{ route('admin.devices') }}" class="admin-tab">
            <i class="fas fa-microchip"></i> Devices
        </a>
        <a href="{{ route('admin.alerts') }}" class="admin-tab">
            <i class="fas fa-bell"></i> Alerts
        </a>
        <a href="{{ route('admin.activity') }}" class="admin-tab">
            <i class="fas fa-history"></i> Activity
        </a>
    </div>
</div>

<!-- Stats -->
<div class="user-stats">
    <div class="user-stat-pill highlight">
        <span class="count">{{ $totalUsers }}</span> Total Users
    </div>
    <div class="user-stat-pill">
        <span class="count" style="color: #be123c;">{{ $adminCount }}</span> Admins
    </div>
    <div class="user-stat-pill">
        <span class="count" style="color: #2563eb;">{{ $farmerCount }}</span> Farmers
    </div>
</div>

<!-- Toolbar -->
<div class="user-toolbar">
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <form action="{{ route('admin.users') }}" method="GET" class="user-search">
            <i class="fas fa-search" style="color: var(--text-muted);"></i>
            <input type="text" name="search" placeholder="Search by name or email..." value="{{ request('search') }}">
        </form>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.users') }}" class="user-filter-btn {{ !request('role') ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.users', ['role' => 'admin']) }}" class="user-filter-btn {{ request('role') === 'admin' ? 'active' : '' }}">Admins</a>
            <a href="{{ route('admin.users', ['role' => 'farmer']) }}" class="user-filter-btn {{ request('role') === 'farmer' ? 'active' : '' }}">Farmers</a>
        </div>
    </div>
    <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')" class="user-filter-btn active" style="background: var(--primary); color: white; border-color: var(--primary);">
        <i class="fas fa-plus"></i> Add User
    </button>
</div>

<!-- User Table -->
<div class="user-table-wrap">
    <div style="overflow-x: auto;">
        <table class="user-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Farms</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="user-name-cell">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-table-avatar">
                            <span class="user-name-text">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">{{ $user->email }}</td>
                    <td><span class="user-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                    <td>
                        <span class="status-dot {{ $user->is_active ? 'active' : 'inactive' }}"></span>
                        <span style="font-size: 0.8rem; color: {{ $user->is_active ? '#166534' : '#dc2626' }}; font-weight: 600;">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--primary);">{{ $user->farms_count }}</td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="action-menu" style="justify-content: flex-end;">
                            <button type="button" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')" class="act-btn" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                @csrf
                                <button type="submit" class="act-btn" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act-btn danger" title="Delete user">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-users" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
                        <span style="font-weight: 600;">No users found</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>

<style>
.um-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.um-modal{background:#fff;border:1px solid var(--border-color);border-radius:16px;padding:1.5rem;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.1)}
.um-modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
.um-modal-header h3{font-size:1.1rem;font-weight:700;color:var(--text-main);margin:0}
.um-modal-close{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.2rem;transition:color .2s}
.um-modal-close:hover{color:#ef4444}
.um-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem}
.um-input{width:100%;padding:.65rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;margin-bottom:1rem}
.um-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.um-actions{display:flex;gap:.75rem;padding-top:.75rem}
.um-btn-cancel{flex:1;padding:.65rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted)}
.um-btn-submit{flex:1;padding:.65rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer}
.hidden{display:none !important}
</style>

<!-- Add User Modal -->
<div id="add-user-modal" class="um-modal-bg hidden">
    <div class="um-modal">
        <div class="um-modal-header">
            <h3>Add New User</h3>
            <button onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="um-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <label class="um-label">Full Name</label>
            <input name="name" required class="um-input" placeholder="e.g. Jane Doe">
            <label class="um-label">Email Address</label>
            <input name="email" type="email" required class="um-input" placeholder="jane@example.com">
            <label class="um-label">Role</label>
            <select name="role" class="um-input">
                <option value="farmer">Farmer</option>
                <option value="admin">Admin</option>
            </select>
            <label class="um-label">Password</label>
            <input name="password" type="password" required class="um-input" placeholder="Minimum 8 characters">
            <div class="um-actions">
                <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="um-btn-cancel">Cancel</button>
                <button type="submit" class="um-btn-submit">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="um-modal-bg hidden">
    <div class="um-modal">
        <div class="um-modal-header">
            <h3>Edit User</h3>
            <button onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="um-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form id="edit-user-form" method="POST" action="">
            @csrf
            @method('PUT')
            <label class="um-label">Full Name</label>
            <input name="name" id="edit-name" required class="um-input">
            <label class="um-label">Email Address</label>
            <input name="email" id="edit-email" type="email" required class="um-input">
            <label class="um-label">Role</label>
            <select name="role" id="edit-role" class="um-input">
                <option value="farmer">Farmer</option>
                <option value="admin">Admin</option>
            </select>
            <label class="um-label">Password (Leave blank to keep current)</label>
            <input name="password" type="password" class="um-input" placeholder="New password (optional)">
            <div class="um-actions">
                <button type="button" onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="um-btn-cancel">Cancel</button>
                <button type="submit" class="um-btn-submit">Update User</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editUser(id, name, email, role) {
        const form = document.getElementById('edit-user-form');
        form.action = `/admin-panel/users/${id}`;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-role').value = role;
        document.getElementById('edit-user-modal').classList.remove('hidden');
    }
</script>

@endsection

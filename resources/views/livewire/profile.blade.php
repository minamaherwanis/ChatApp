<div class="profile-container">
    <div class="profile-card">


        <div class="profile-header">

            <a href="javascript:history.back()" class="back-btn">
                ←
            </a>
            <div class="profile-avatar-container">
                <div class="profile-avatar" id="avatarPreview">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" alt="preview">
                    @elseif ($profile->avatar)
                        <img src="{{ asset('storage/' . $profile->avatar) }}?v={{ time() }}"
                            wire:key="avatar-{{ $profile->avatar }}" alt="profile">
                    @else
                        <span>{{ strtoupper(substr($profile?->name ?? $user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="change-avatar-btn" onclick="document.getElementById('avatarInput').click()">
                    ✏️
                </div>
                <input type="file" id="avatarInput" accept="image/*" style="display: none;" wire:model="avatar">
            </div>
        </div>

        <!-- Content -->
        <div class="profile-content">

            <!-- Name & Username -->
            <h1 class="profile-name" id="displayName">{{ $profile->name }}</h1>
            <p class="profile-username">{{ $phonenumber }}</p>

            <!-- Tabs -->
            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab(0)">View Profile</button>
                <button class="tab-btn" onclick="switchTab(1)">Edit Profile</button>
            </div>

            <!-- View Profile Tab -->
            <div id="viewTab" class="tab-content">
                @if (session()->has('message'))
                    <div class="success-message">
                        {{ session('message') }}
                    </div>
                @endif
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $profile->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value">{{ $profile->username }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $email }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Bio</div>
                        <div class="info-value">{{ $profile->bio }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Joined</div>
                        <div class="info-value">{{ $profile->created_at->format('F Y') }}</div>
                    </div>

                </div>
                <div class="account-actions">
                    <button type="button" class="btn-logout" wire:click="logout">
                        Logout
                    </button>

                    <button type="button" class="btn-delete"
                        onclick="confirm('Are you sure you want to delete your account?') || event.stopImmediatePropagation()"
                        wire:click="deleteAccount">
                        Delete Account
                    </button>
                </div>
            </div>

            <!-- Edit Profile Tab -->
            <div id="editTab" class="tab-content" style="display: none;">


                <form wire:submit.prevent="save">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" wire:model="name">
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" wire:model="username">
                        @error('username')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" wire:model="email">
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea class="form-control textarea" wire:model="bio"></textarea>
                        @error('bio')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function switchTab(tabIndex) {
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            btn.classList.toggle('active', i === tabIndex);
        });

        document.getElementById('viewTab').style.display = tabIndex === 0 ? 'block' : 'none';
        document.getElementById('editTab').style.display = tabIndex === 1 ? 'block' : 'none';
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap');

    *,
    *::before,
    *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        background: #0a1421;
        color: #e0e7ff;
        font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
        -webkit-font-smoothing: antialiased;
    }

    .back-btn {
        position: absolute;
        top: 15px;
        left: 15px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.4);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        text-decoration: none;
        transition: 0.2s;
        backdrop-filter: blur(8px);
    }

    .back-btn:hover {
        background: rgba(0, 0, 0, 0.6);
        transform: scale(1.05);
    }

    .account-actions {
        display: flex;
        flex-direction: column;
        /* يخليهم تحت بعض */
        gap: 10px;
        margin-top: 20px;
    }

    .account-actions button {
        width: 100%;
        /* ياخد عرض السطر كله */
    }

    /* Logout */
    .btn-logout {
        background: #444;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
    }

    /* Delete */
    .btn-delete {
        background: #e53935;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
    }

    .success-message {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #4ade80;
        padding: 14px 20px;
        border-radius: 14px;
        margin-bottom: 22px;
        font-size: 0.95rem;
        text-align: center;
    }

    .error-message {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #f87171;
        padding: 14px 20px;
        border-radius: 14px;
        margin-bottom: 22px;
        font-size: 0.95rem;
        text-align: center;
    }

    .error {
        color: #f87171;
        font-size: 0.82rem;
        margin-top: 6px;
        display: block;
    }

    .form-control.is-invalid {
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .profile-container {
        max-width: 820px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .profile-card {
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(148, 163, 184, 0.08);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
    }

    /* Header */
    .profile-header {
        height: 180px;
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        position: relative;
    }

    .profile-avatar-container {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: -60px;
    }

    .profile-avatar {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        border: 6px solid #0a1421;
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.2rem;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.5);
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .profile-avatar:hover {
        transform: scale(1.05);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .change-avatar-btn {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    /* Content */
    .profile-content {
        padding: 80px 40px 40px;
    }

    .profile-name {
        text-align: center;
        font-size: 1.85rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .profile-username {
        text-align: center;
        color: #94a3b8;
        font-size: 1.05rem;
        margin-bottom: 30px;
    }

    /* Tabs */
    .profile-tabs {
        display: flex;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        margin-bottom: 30px;
    }

    .tab-btn {
        padding: 14px 28px;
        background: none;
        border: none;
        color: #94a3b8;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        color: #60a5fa;
        border-bottom: 3px solid #60a5fa;
    }

    /* View Mode */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }

    .info-item {
        background: rgba(30, 41, 59, 0.4);
        padding: 18px 22px;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.08);
    }

    .info-label {
        font-size: 0.82rem;
        color: #94a3b8;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .info-value {
        font-size: 1.05rem;
        color: #e0e7ff;
        word-break: break-all;
    }

    /* Edit Form */
    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.9rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 14px;
        color: #e0e7ff;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #60a5fa;
        background: rgba(30, 41, 59, 0.9);
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.15);
    }

    .textarea {
        min-height: 110px;
        resize: vertical;
    }

    .btn-save {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: 9999px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px -5px rgba(59, 130, 246, 0.5);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .profile-content {
            padding: 70px 20px 30px;
        }
    }
</style>

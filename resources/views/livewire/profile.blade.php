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
                        <div style="width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,#4fa2ff,#1a6fd4);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;letter-spacing:1px;">
    {{ strtoupper(substr($profile?->name ?? $user->name, 0, 2)) }}
</div>
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
  
</style>

@extends('layouts.app')

@section('title', 'Complete Profile | ChatApp')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        .profile-shell {
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 2rem;
            background: radial-gradient(circle at top, rgba(79, 162, 255, 0.12), transparent 35%),
                linear-gradient(180deg, #08131f 0%, #08131f 100%);
        }

        .profile-panel {
            width: min(100%, 660px);
            background: #0f1b2b;
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.25);
            display: grid;
            gap: 1.5rem;
        }

        .profile-panel h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.5vw, 2.5rem);
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .field-row {
            display: grid;
            gap: 0.75rem;
        }

        .field-row label {
            color: var(--muted);
            font-size: 0.95rem;
            font-weight: 600;
        }

        .field-row input,
        .field-row textarea {
            width: 100%;
            min-height: 3.75rem;
            background: #15263d;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 1rem;
            color: #f8fbff;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field-row textarea {
            min-height: 8rem;
            resize: vertical;
        }

        .field-row input:focus,
        .field-row textarea:focus {
            border-color: rgba(79, 162, 255, 0.65);
            box-shadow: 0 0 0 4px rgba(79, 162, 255, 0.12);
        }

        .photo-upload {
            display: grid;
            gap: 0.75rem;
        }

        .photo-preview {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1rem;
            align-items: center;
        }

        .photo-preview img {
            width: 96px;
            height: 96px;
            border-radius: 22px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .photo-preview p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .photo-input-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .photo-input-row .photo-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.75rem;
            padding: 0 1.25rem;
            color: #4fa2ff;
            font-weight: 700;
            border-radius: 18px;
            border: 1px solid rgba(79, 162, 255, 0.25);
            background: rgba(79, 162, 255, 0.08);
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
        }

        .photo-input-row .photo-button:hover {
            background: rgba(79, 162, 255, 0.14);
            transform: translateY(-1px);
        }

        .photo-input-row .photo-filename {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.95rem;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .photo-upload input[type="file"] {
            display: none;
        }

        .btn-primary {
            width: 100%;
            justify-self: stretch;
        }

        @media (max-width: 760px) {
            .field-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="profile-shell">
        <section class="profile-panel">
            <div>
                <span class="eyebrow">Step 2 of 2</span>
                <h1>Complete your profile</h1>
                <p class="lead">Add a few details so we can personalize your chat experience.</p>
            </div>

            <form class="login-form" action="{{ route('save.profile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="field-grid">
                    <div class="field-row">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="field-row">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="field-row">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>

                <div class="field-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="field-row">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" placeholder="Tell us a bit about yourself" rows="5"></textarea>
                </div>

                <div class="photo-upload">
                    <label for="photo">Profile Photo</label>

                    <div class="photo-preview" id="photoPreviewWrapper">
                        <img src="https://via.placeholder.com/96x96.png?text=Photo" alt="Profile preview" id="photoPreviewImage">
                        <p id="photoPreviewText">Choose a profile photo to make your account feel more personal.</p>
                    </div>

                    <div class="photo-input-row">
                        <label class="photo-button" for="photo">Upload photo</label>
                        <span class="photo-filename" id="photoFilename">No file chosen</span>
                    </div>

                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>

                <button type="submit" class="btn-primary">Save profile</button>
            </form>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoInput = document.getElementById('photo');
            const previewImage = document.getElementById('photoPreviewImage');
            const previewText = document.getElementById('photoPreviewText');
            const filenameText = document.getElementById('photoFilename');

            photoInput.addEventListener('change', function () {
                const file = photoInput.files && photoInput.files[0];

                if (!file) {
                    previewImage.src = 'https://via.placeholder.com/96x96.png?text=Photo';
                    previewImage.alt = 'Profile preview';
                    filenameText.textContent = 'No file chosen';
                    previewText.textContent = 'Choose a profile photo to make your account feel more personal.';
                    return;
                }

                filenameText.textContent = file.name;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewImage.src = event.target.result;
                        previewImage.alt = file.name;
                    };
                    reader.readAsDataURL(file);
                    previewText.textContent = 'Preview your selected photo before saving.';
                } else {
                    filenameText.textContent = 'Invalid file type';
                }
            });
        });
    </script>
@endsection

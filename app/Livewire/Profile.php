<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Otp;
use App\Models\Profile as ModelsProfile;
use App\Models\Story;
use App\Models\StoryView;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Profile extends Component
{
    use WithFileUploads;
    public $user;
    public $profile;
    public $name;
    public $username;
    public $email;
    public $bio;
    public $avatar;

    public $phonenumber;


    public function save()
    {


        $this->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:profiles,username,' . $this->profile->id,
            'email'    => 'required|email|unique:profiles,email,' . $this->profile->id,
            'bio'      => 'nullable|string|max:500',
            'avatar'   => 'nullable|image|max:5120'
        ]);


        $data = [
            'name'     => $this->name,
            'email'    => $this->email,
            'username' => $this->username,
            'bio'      => $this->bio,
        ];

        if ($this->avatar) {
            if ($this->profile->avatar) {
                \Storage::disk('public')->delete($this->profile->avatar);
            }
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }
        $this->profile->update($data);
        $this->profile = $this->profile->fresh();
        $this->avatar = null;

        session()->flash('message', 'Profile updated successfully!');
    }



    public function mount($id = null)
    {
        $id = $id ?? Auth::id();

        $this->user = User::with('profile')->findOrFail($id);
        $this->phonenumber = $this->user->phone;
        $this->profile = $this->user->profile;

        $this->name = $this->profile->name;
        $this->username = $this->profile->username;
        $this->email = $this->profile->email;
        $this->bio = $this->profile->bio;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function deleteAccount()
    {
        $user = auth()->user();

        DB::transaction(function () use ($user) {

            // Messages
            Message
                ::where('sender_id', $user->id)
                ->delete();

            // Chats
            Chat::where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id)
                ->delete();

            // Stories Views
            StoryView::where('user_id', $user->id)
                ->delete();

            // Stories
            Story::where('user_id', $user->id)
                ->delete();

            // OTP
            Otp::where('phone', $this->phonenumber)
                ->delete();

            // Profile
            \App\Models\Profile::where('user_id', $user->id)
                ->delete();
            $user->delete();
        });

        Auth::logout();

        return redirect('/login');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilePage extends Component
{
    public $name;
    public $email;

    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email'
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email
        ]);

        session()->flash('success', 'Profile berhasil diupdate!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|same:new_password_confirmation',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password lama salah');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->reset([
            'current_password',
            'new_password',
            'new_password_confirmation'
        ]);

        session()->flash('success', 'Password berhasil diubah!');
    }

    public function render()
    {
        return view('livewire.profile-page');
    }
}
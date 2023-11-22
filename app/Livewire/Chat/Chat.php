<?php

namespace App\Livewire\Chat;

use App\Models\Contact;
use Livewire\Component;

class Chat extends Component
{
    public $search;

    /**
     * Computed property
     */
    public function getContactsProperty()
    {
        return Contact::where('user_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('email', 'like', '%' . $this->search . '%');
                        });
                });
            })->get() ?? [];
    }

    public function render()
    {
        return view('livewire.chat.chat')->layout('layouts.chat');
    }
}

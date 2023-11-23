<?php

namespace App\Livewire\Chat;

use App\Models\Contact;
use Livewire\Component;

class Chat extends Component
{
    public $search;
    public $contactChat;
    public $chat;

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

    public function openChatContact(Contact $contact)
    {
        $chat = auth()->user()->chats()
            ->whereHas('users', function ($query) use ($contact) {
                $query->where('user_id', $contact->contact_id);
            })
            ->has('users', 2)
            ->first();

        if ($chat) {
            $this->chat = $chat;
        } else {
            $this->contactChat = $contact;
        }
    }

    public function render()
    {
        return view('livewire.chat.chat')->layout('layouts.chat');
    }
}

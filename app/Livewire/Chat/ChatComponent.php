<?php

namespace App\Livewire\Chat;

use App\Models\Chat;
use App\Models\Contact;
use Livewire\Component;

class ChatComponent extends Component
{
    public $search;
    public $contactChat;
    public $chat;
    public $bodyMessage;

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

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => 'required'
        ]);

        if (!$this->chat) {
            $this->chat = Chat::create();

            // Attach users to chat
            $this->chat->users()->attach([
                auth()->id(),
                $this->contactChat->contact_id
            ]);
        }

        // Create message
        $this->chat->messages()->create([
            'body' => $this->bodyMessage,
            'user_id' => auth()->id(),
        ]);

        $this->reset('bodyMessage', 'contactChat');
    }

    public function render()
    {
        return view('livewire.chat.chat-component')->layout('layouts.chat');
    }
}

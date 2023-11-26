<?php

namespace App\Livewire\Chat;

use App\Models\Chat;
use App\Models\Contact;
use Livewire\Component;
use App\Notifications\NewMessage;
use App\Notifications\UserTyping;
use Illuminate\Support\Facades\Notification;

class ChatComponent extends Component
{
    public $search;
    public $contactChat;
    public $chat;
    public $chatId;
    public $bodyMessage;

    public function getListeners()
    {
        $userId = auth()->id();

        // Listen to events with Laravel Echo
        return [
            // Listen to notification and render, the channel is built using pusher and laravel echo.
            "echo-notification:App.Models.User.{$userId},notification" => "render",
        ];
    }

    /**
     * Computed properties
     */

    /**
     * Get contacts from user
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

    /**
     *  Get messages from chat
     */
    public function getMessagesProperty()
    {
        return $this->chat ? $this->chat->messages()->get() : [];
    }

    /**
     * Get chats from user
     */
    public function getChatsProperty()
    {
        return auth()->user()->chats()->get()->sortByDesc('last_message_at');
    }

    /**
     * Get chat users for notifications
     */
    public function getChatUsersForNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : [];
    }

    /**
     * Hooks
     */

    /**
     * Updated body message and notify user when typing
     */
    public function updatedBodyMessage($value)
    {
        if ($value) {
            Notification::send($this->chat_users_for_notifications, new UserTyping($this->chat->id));
        }
    }

    /**
     * Methods
     */

    /**
     * Open chat with contact
     */
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

            // For notify user when typing
            $this->chatId = $chat->id;

            $this->reset('contactChat', 'bodyMessage', 'search');
        } else {
            $this->contactChat = $contact;

            $this->reset('chat', 'bodyMessage', 'search');
        }
    }

    /**
     * Open chat existing
     */
    public function openChatExisting(Chat $chat)
    {
        $this->chat = $chat;

        // For notify user when typing
        $this->chatId = $chat->id;

        $this->reset('contactChat', 'bodyMessage');
    }

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => 'required'
        ]);

        if (!$this->chat) {
            $this->chat = Chat::create();

            // For notify user when typing
            $this->chatId = $this->chat->id;

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

        // Notify new message to user of chat
        Notification::send($this->chat_users_for_notifications, new NewMessage());

        $this->reset('bodyMessage', 'contactChat');
    }

    public function render()
    {
        if ($this->chat) {
            $this->dispatch('scrollToEnd');
        }

        return view('livewire.chat.chat-component')->layout('layouts.chat');
    }
}

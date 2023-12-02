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
    public $users;

    /**
     * Nota:
     * - Los canales de presencia se utilizan para rastrear qué usuarios están
     * actualmente conectados a un canal dado. Estos canales son especialmente
     * útiles en aplicaciones de chat en tiempo real, donde se necesita conocer
     * quién está en línea.
     */

    public function getListeners()
    {
        $userId = auth()->id();

        // Listen to events with Laravel Echo
        return [
            // Listen to notification and render, the channel is built using pusher and laravel echo.
            "echo-notification:App.Models.User.{$userId},notification" => "render",

            // Channels presence
            "echo-presence:chat.1,here" => "hereChat",
            "echo-presence:chat.1,joining" => "joiningChat",
            "echo-presence:chat.1,leaving" => "leavingChat",
        ];
    }

    public function mount()
    {
        $this->users = collect();
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
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : collect();
    }

    /**
     * Get active chat
     */
    public function getActiveChatProperty()
    {
        // Verifica si la lista de usuarios ($this->users) contiene el ID del primer usuario en la lista de usuarios del chat.
        return $this->users->contains($this->chat_users_for_notifications->first()->id);
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

    /**
     * Se llama cuando se inicia el componente o cuando un usuario nuevo se une al canal.
     * $users Lista de usuarios presentes en el canal en ese momento.
     */
    public function hereChat($users)
    {
        // Actualiza la propiedad $users con la lista de usuarios presentes en el canal.
        $this->users = collect($users)->pluck('id');
    }

    /**
     * Se llama cuando un usuario se une al canal.
     * $user Información sobre el usuario que se unió al canal.
     */
    public function joiningChat($user)
    {
        // Agrega el ID del usuario que se unió a la lista de usuarios presentes en el canal.
        $this->users->push($user['id']);
    }

    /**
     * Se llama cuando un usuario abandona el canal.
     * $user Información sobre el usuario que abandonó el canal.
     */
    public function leavingChat($user)
    {
        // Filtra la lista de usuarios para excluir al usuario que abandonó el canal.
        $this->users = $this->users->filter(function ($id) use ($user) {
            return $id != $user['id'];
        });
    }

    public function readMessages()
    {
        if ($this->chat) {
            // Marcar mensajes como leídos
            $isRead = $this->chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
                'is_read' => true
            ]);

            if ($isRead) {
                Notification::send($this->chat_users_for_notifications, new NewMessage());
            }
        }
    }

    public function render()
    {
        if ($this->chat) {
            // Se llama en render para que se ejecute cada vez que se envia un mensaje
            $this->readMessages();


            $this->dispatch('scrollToEnd');
        }

        return view('livewire.chat.chat-component')->layout('layouts.chat');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'is_group',
    ];


    /*
     * Attributes
     */

    /**
     * Get name
     */
    public function name(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if ($this->is_group) {
                    return $value;
                }

                $user = $this->users->where('id', '!=', auth()->id())->first();
                $contact = auth()->user()->contacts()->where('contact_id', $user->id)->first();

                return $contact ? $contact->name : $user->email;
            }
        );
    }

    /**
     * Get image url
     */
    public function image(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->is_group) {
                    return Storage::url($this->image_url);
                }

                $user = $this->users->where('id', '!=', auth()->id())->first();

                return $user->profile_photo_url;
            }
        );
    }

    /**
     * Get last message
     */
    public function lastMessageAt(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->messages->last()->created_at;
            }
        );
    }

    /**
     * Get unread message number
     */
    public function unreadMessages(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->messages->where('user_id', '!=', auth()->id())->where('is_read', false)->count();
            }
        );
    }

    /*
     * Relationships
     */

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

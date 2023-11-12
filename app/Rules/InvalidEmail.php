<?php

namespace App\Rules;

use Closure;
use App\Models\Contact;
use Illuminate\Contracts\Validation\ValidationRule;

class InvalidEmail implements ValidationRule
{
    public $email;

    public function __construct($email = null)
    {
        $this->email = $email;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $contact = Contact::where('user_id', auth()->id())
            ->whereHas('user', function ($query) use ($value) {
                $query->where('email', $value)
                    // Solo se ejecuta cuando se le pasa un valor al constructor - new InvalidEmail($email)
                    ->when($this->email, function ($query) {
                        $query->where('email', '!=', $this->email);
                    });
            })->get();

        if ($contact->count() > 0) {
            $fail('El email ya se encuentra en la lista de contactos.');
        }
    }
}

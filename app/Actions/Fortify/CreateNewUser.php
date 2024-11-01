<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\PrivateKey;
use App\Services\EncryptionService;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $keys = EncryptionService::generateRSAKeys();

        $newUser = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'public_key' => $keys['public_key']
        ]);

        // Armazena a private_key na sessão
        session(['private_key' => $keys['private_key']]);

        auth()->login($newUser);

        return $newUser;
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Inloggen in de beheeromgeving.
 *
 * Na MAX_POGINGEN foute pogingen moet je een minuut wachten, tegen het raden
 * van wachtwoorden. Alleen foute pogingen tellen mee; wie goed inlogt, begint
 * weer bij nul.
 */
class LoginRequest extends FormRequest
{
    public const MAX_POGINGEN = 5;

    /**
     * Inloggen mag iedereen proberen; de guest-middleware houdt wie al
     * ingelogd is weg van het formulier.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Log de beheerder in, of gooi een foutmelding bij het e-mailveld terug.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            // Bewust één melding voor beide gevallen, zodat je niet kunt
            // uitzoeken welke e-mailadressen een account hebben.
            throw ValidationException::withMessages([
                'email' => 'Onjuist e-mailadres of wachtwoord.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_POGINGEN)) {
            return;
        }

        $seconden = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Te veel pogingen. Probeer het over {$seconden} seconden opnieuw.",
        ]);
    }

    /**
     * Per e-mailadres én IP-adres, zodat iemand anders jou niet kan
     * buitensluiten door vanaf zijn eigen netwerk jouw e-mailadres te proberen.
     */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}

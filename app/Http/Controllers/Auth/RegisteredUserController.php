<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['vat_number' => self::normalizeVat($request->input('vat_number'))]);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['required', 'in:individual,company'],
            'company_name' => ['exclude_unless:account_type,company', 'required', 'string', 'max:120'],
            'vat_number' => ['exclude_unless:account_type,company', 'required', function (string $attribute, mixed $value, \Closure $fail) {
                if (! self::isValidBelgianVat($value)) {
                    $fail('Ce numéro de TVA belge n’est pas valide (format BE0123456789).');
                }
            }],
        ]);
        $company = $request->account_type === 'company';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'company_name' => $company ? $request->company_name : null,
            'vat_number' => $company ? $request->vat_number : null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }

    /** « be 0123.456.749 » devient « BE0123456749 » ; un numéro à 9 chiffres reçoit son 0 initial. */
    private static function normalizeVat(?string $vat): ?string
    {
        if ($vat === null || trim($vat) === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', $vat);

        return 'BE'.(strlen($digits) === 9 ? '0'.$digits : $digits);
    }

    /** Numéro d'entreprise belge : 10 chiffres commençant par 0 ou 1, les deux derniers = 97 − (les 8 premiers modulo 97). */
    private static function isValidBelgianVat(?string $vat): bool
    {
        if ($vat === null || ! preg_match('/^BE([01]\d{7})(\d{2})$/', $vat, $m)) {
            return false;
        }

        return 97 - ((int) $m[1] % 97) === (int) $m[2];
    }
}

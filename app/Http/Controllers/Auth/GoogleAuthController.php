<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.google_login_failed')]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.google_email_required')]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: __('auth.google_user_default_name'),
                'email' => $email,
                'password' => null,
            ]);

            $user->google_id = $googleUser->getId();
            $user->email_verified_at = now();
            $user->save();
        } elseif (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            $user->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}


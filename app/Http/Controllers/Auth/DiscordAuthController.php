<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class DiscordAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('discord')
            ->scopes(['identify', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.discord_login_failed')]);
        }

        $email = $discordUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.discord_email_required')]);
        }

        $user = User::query()
            ->where('discord_id', $discordUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $discordUser->getName() ?: $discordUser->getNickname() ?: __('auth.discord_user_default_name'),
                'email' => $email,
                'password' => null,
            ]);

            $user->discord_id = $discordUser->getId();
            $user->email_verified_at = now();
            $user->save();
        } elseif (! $user->discord_id) {
            $user->discord_id = $discordUser->getId();
            $user->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

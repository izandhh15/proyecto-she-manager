<?php

namespace App\Listeners;

use App\Models\DeviceSession;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogDeviceSession
{
    public function __construct(
        private Request $request,
    ) {}

    public function handle(Login $event): void
    {
        $userAgent = (string) $this->request->userAgent();
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            $deviceType = 'tablet';
        } elseif (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            $deviceType = 'mobile';
        } else {
            $deviceType = 'desktop';
        }

        $browser = match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'chrome/') => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/') => 'Safari',
            default => null,
        };

        $os = match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios') => 'iOS',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            default => null,
        };

        DeviceSession::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
            'logged_in_at' => now(),
        ]);
    }
}

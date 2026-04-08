<?php

namespace App\Providers;

use App\Services\LogSmsSender;
use App\Services\SmsSender;
use App\Services\TwilioSmsSender;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsSender::class, function () {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            if (is_string($sid) && $sid !== '' && is_string($token) && $token !== '' && is_string($from) && $from !== '') {
                return new TwilioSmsSender(new Client($sid, $token), $from);
            }

            return new LogSmsSender();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(120)->by($key);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');
            return Limit::perMinute(10)->by(strtolower($email) . '|' . $request->ip());
        });
    }
}

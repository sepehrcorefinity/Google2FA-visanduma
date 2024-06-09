<?php


namespace Visanduma\NovaTwoFactor\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Nette\Utils\Html;
use PragmaRX\Google2FA\Google2FA as G2fa;
use Visanduma\NovaTwoFactor\TwoFaAuthenticator;

class TwoFa
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function handle($request, Closure $next)
    {

        $except = [
            'nova-vendor/nova-two-factor/authenticate',
            'nova-vendor/nova-two-factor/recover',
            'nova-vendor/nova-two-factor/viasms',
            'nova/logout'
        ];

        $exceptTwo = [
            'nova-vendor/nova-two-factor/selection',
            'nova-vendor/nova-two-factor/setgoogle',
            'nova-vendor/nova-two-factor/setsms',
            'logout',
            '/'
        ];


        if (!config('nova-two-factor.enabled') || in_array($request->path(), $except)) {
            return $next($request);
        }

        $authenticator = app(TwoFaAuthenticator::class)->boot($request);
        if (auth()->guest() || $authenticator->isAuthenticated()) {
            return $next($request);
        }


        if ($request->path() === 'nova-vendor/nova-two-factor/checksms') {
            if ($request->sms !== null) {
                return $next($request);
            } else {
                return redirect()->to(config('nova.path'));
            }
        }

        $authenticator = app(TwoFaAuthenticator::class)->boot($request);
        if($authenticator->isAuthenticated() === false) {
            if (auth()->user()->twoFa->google2fa_enable && auth()->user()->sms_two_fa_enabled) {
                return response(view('nova-two-factor::sign-in'));
            } elseif (auth()->user()->twoFa->google2fa_enable && auth()->user()->sms_two_fa_enabled === 0) {
                return response(view('nova-two-factor::sign-in'));
            }elseif (auth()->user()->twoFa->google2fa_enable === 0 && auth()->user()->sms_two_fa_enabled) {
                return response(redirect()->route('nova-two-factor.viasms'));
            }
        }else
        {
            return redirect()->to(config('nova.path'));
        }

        $authenticator = app(TwoFaAuthenticator::class)->boot($request);
        if (!auth()->user()->twoFa && auth()->user()->sms_two_fa_enabled === 0 && $authenticator->isAuthenticated() === false) {
            if (in_array($request->path(), $exceptTwo)) {
                return $next($request);
            } else {
                return redirect()->to(config('nova.path'));
            }
        }

        $authenticator = app(TwoFaAuthenticator::class)->boot($request);
        if (auth()->user()->twoFa->google2fa_enable === 0 && auth()->user()->sms_two_fa_enabled === 0 && $authenticator->isAuthenticated() === false) {
            if (in_array($request->path(), $exceptTwo)) {
                return $next($request);
            } else {
                return redirect()->to(config('nova.path'));
            }
        }
    }

}

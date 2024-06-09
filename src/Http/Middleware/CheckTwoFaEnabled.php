<?php

namespace Visanduma\NovaTwoFactor\Http\Middleware;

use App\Models\Crm\User;
use Closure;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use phpseclib3\Crypt\Hash;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class CheckTwoFaEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->path() === '/'){
            $checkenv = env('CHECK_TWO_FA');
            if ($checkenv) {
                $googleStatus = $request->user()->twoFa->google2fa_enable ?? 0;
                $smsStatus = $request->user()->sms_two_fa_enabled;
                if ($googleStatus || $smsStatus) {
                    return $next($request);
                }
                else{
                    return redirect()->route('nova-two-factor.selction');
                }
            } else {
                $twofaClass = new Authenticator($request);
                $twofaClass->login();
                return $next($request);
            }
        }
        else
        {
            return $next($request);
        }
    }
}

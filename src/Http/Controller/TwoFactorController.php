<?php

namespace Visanduma\NovaTwoFactor\Http\Controller;


use App\Http\Controllers\Controller;
use App\Models\System\Config;
use App\Services\TwilioSMS\SMS;
use DigitalOceanV2\Api\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use phpseclib3\File\ASN1\Element;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FA\Google2FA as G2fa;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use PragmaRX\Google2FALaravel\Support\Constants;
use Twilio\Rest\Client;
use Visanduma\NovaTwoFactor\Http\Middleware\CheckTwoFaEnabled;
use Visanduma\NovaTwoFactor\Models\TwoFa;
use Visanduma\NovaTwoFactor\TwoFaAuthenticator;
use function Symfony\Component\VarDumper\Dumper\esc;

class TwoFactorController extends Controller
{
    public function registerUser()
    {

        if (auth()->user()->twoFa && auth()->user()->twoFa->confirmed == 1) {
            return response()->json([
                'message' => 'Already verified !'
            ]);
        }

        $google2fa = new G2fa();
        $secretKey = $google2fa->generateSecretKey();

        $recoveryKey = strtoupper(Str::random(16));
        $recoveryKey = str_split($recoveryKey, 4);
        $recoveryKey = implode("-", $recoveryKey);

        $recoveryKeyHashed = bcrypt($recoveryKey);

        $data['recovery'] = $recoveryKey;


        $userTwoFa = new TwoFa();
        $userTwoFa::where('user_id', auth()->user()->id)->delete();
        $user2fa = new $userTwoFa();
        $user2fa->user_id = auth()->user()->id;
        $user2fa->google2fa_secret = $secretKey;
        $user2fa->recovery = $recoveryKeyHashed;
        $user2fa->save();


        $google2fa_url = $this->getQRCodeGoogleUrl(
            config('app.name'),
            auth()->user()->email,
            $secretKey
        );

        $data['google2fa_url'] = $google2fa_url;

        return $data;
    }

    public function verifyOtp()
    {
        $otp = request()->get('otp');
        request()->merge(['one_time_password' => $otp]);

        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if ($authenticator->isAuthenticated()) {
            // otp auth success!

            auth()->user()->twoFa()->update([
                'confirmed' => true,
                'google2fa_enable' => true
            ]);

            return response(redirect('/profile#two-factor-auth')->with('message', '2FA security successfully activated !'));
        }

        // auth fail
        return response()->json([
            'message' => 'Invalid OTP !. Please try again'
        ], 422);
    }

    public function getStatus()
    {
        $user = auth()->user();

        $res = [
            "registered" => !empty($user->twoFa),
            "enabled" => auth()->user()->twoFa->google2fa_enable ?? false,
            "confirmed" => auth()->user()->twoFa->confirmed ?? false
        ];
        return $res;
    }

    public function getQRCodeGoogleUrl($company, $holder, $secret, $size = 200)
    {
        $g2fa = new G2fa();
        $url = $g2fa->getQRCodeUrl($company, $holder, $secret);

        return self::generateGoogleQRCodeUrl('https://quickchart.io/', 'qr', 'text=' . $url . '&size' . $size, $url);
    }

    public static function generateGoogleQRCodeUrl($domain, $page, $queryParameters, $qrCodeUrl)
    {
        $url = $domain .
            rawurlencode($page) .
            '?' . $queryParameters .
            urlencode($qrCodeUrl);

        return $url;
    }

    public function authenticate(Request $request)
    {


        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if ($authenticator->isAuthenticated()) {
            return redirect()->to(config('nova.path'));
        }

        return back()->withErrors(['Incorrect OTP !']);
    }

    public function recover(Request $request)
    {
        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if (!$authenticator->isAuthenticated()) {
            if (auth()->user()->twoFa->google2fa_enable === 1) {
                if ($request->isMethod('get')) {
                    return view('nova-two-factor::recover');
                }

                if (Hash::check($request->get('recovery_code'), auth()->user()->twoFa->recovery)) {
                    // reset 2fa
                    auth()->user()->twoFa()->delete();
                    return redirect()->to(config('nova.path'));
                } else {
                    return back()->withErrors(['Incorrect recovery code !']);
                }
            } else {
                return redirect()->route('nova-two-factor.viasms');
            }
        } else {
            return redirect()->back();
        }

    }

    public function sendViaSms(Request $request)
    {
        $authenticator = app(TwoFaAuthenticator::class)->boot(request());
        $user = $request->user();
        if ($authenticator->isAuthenticated() === false) {
            if ($user->sms_two_fa_enabled) {
                $code = $user->mobile_verification_code;
                if ($user->mobile_verification_code === null) {
                    $code = rand(11111, 99999);
                    $user->update(['mobile_verification_code' => $code]);
                }
                $message = "Your two factor authentication code is $code";
                (new SMS())->sendSimple($user->mobile, $message);
                return view('nova-two-factor::viasms');
            } else {
                return redirect()->to(config('nova.path'));
            }
        }
        else{
            return redirect()->back();
        }

    }

    public function checkTwilio(Request $request)
    {
        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if (!$authenticator->isAuthenticated()) {
            $inputsms = $request->sms;
            $realcode = $request->user()->mobile_verification_code;

            if ($inputsms !== $realcode) {
                $errorMessage = "Invalid Verification Code !";
                return redirect()->route('nova-two-factor.viasms')->withErrors([$errorMessage]);
            } else {
                auth()->user()->update(['mobile_verification_code' => null]);
                $twofaClass = new Authenticator($request);
                $twofaClass->login();
                return redirect()->to(config('nova.path'));
            }
        } else {
            return redirect('/');
        }


    }

    public function ShowSelection()
    {
        $authenticator = app(TwoFaAuthenticator::class)->boot(request());
        if ($authenticator->isAuthenticated() === false) {
            return view('nova-two-factor::selection');
        } else {
            return redirect()->back();
        }
    }

    public function SetSmsTwoFa()
    {
        $model = auth()->user();
        $model->sms_two_fa_enabled = 1;
        $model->save();
        auth()->logout();
        return redirect()->to(config('nova.path'));
    }

    public function SetGoogleTwoFa()
    {
        $model = auth()->user()->twoFa;
        $model->google2fa_enable = 1;
        $model->save();
        auth()->logout();
        return redirect()->to(config('nova.path'));
    }

}

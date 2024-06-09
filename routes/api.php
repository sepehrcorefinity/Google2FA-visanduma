<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lifeonscreen\Google2fa\Google2FAAuthenticator;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/register', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'registerUser']);

Route::match(['get','post'],'/recover', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'recover'])->name('nova-two-factor.recover');

Route::get('/status', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'getStatus']);

Route::get('/viasms', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'sendViaSms'])->name('nova-two-factor.viasms');

Route::post('/confirm', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'verifyOtp']);

Route::post('/authenticate', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'authenticate'])->name('nova-two-factor.auth');

Route::post('/checksms', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'checkTwilio'])->name('nova-two-factor.ckecksms');

Route::get('/selection', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'ShowSelection'])->name('nova-two-factor.selction');

Route::get('/setsms', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'SetSmsTwoFa'])->name('nova-two-factor.setsms');

Route::get('/setgoogle', [\Visanduma\NovaTwoFactor\Http\Controller\TwoFactorController::class,'SetGoogleTwoFa'])->name('nova-two-factor.setgoogle');

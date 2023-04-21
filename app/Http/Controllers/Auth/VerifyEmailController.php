<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request)//: RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
//            return redirect()->intended(
//                config('app.frontend_url') . RouteServiceProvider::HOME . '?verified=1'
//            );
            return response()->noContent();
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
//        return redirect()->intended(
//            config('app.frontend_url') . RouteServiceProvider::HOME . '?verified=1'
//        );
        return response([
            "message" => "Email is not verified.",
        ], 403);
    }
}

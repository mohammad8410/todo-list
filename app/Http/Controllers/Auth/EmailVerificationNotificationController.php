<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)//: JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // return redirect()->intended(RouteServiceProvider::HOME);
            return response()->noContent();
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}

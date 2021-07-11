<?php

namespace Modules\Otp\Http\Controllers\Client;

use Modules\Core\Http\Controllers\Admin\AdminBaseController;
use Modules\Otp\Entities\OneTimePassword;
use Modules\User\Contracts\Authentication;
use Illuminate\Http\Request;

class OtpController extends AdminBaseController
{
    /**
     * @var Authentication
     */
    private $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Shows the OTP login screen
     *
     * @return View/Redirect
     */
    public function view(Request $request)
    {
        // this route is protected by WEB and AUTH middlewares, but still, this check can be useful.
        if ($this->auth->check()) {
            
            // Check if user has already made a OTP request with a "waiting" status
            $otp = OneTimePassword::where([
                "user_id" => $this->auth->id(),
                "status" => "waiting"
            ])->orderByDesc("created_at")->first();

            // if it exists
            if ($otp instanceof OneTimePassword) {

                // show the OTP validation form
                return view('otp::client.otpvalidate');
            } else {

                // the user hasn't done a request, why is he/she here? redirect back to login screen.
                $this->auth->logout();
                return redirect('/')->withErrors(["email" => trans("otp::messages.otp_expired")]);
            }
        } else {

            // the user hasn't tried to log in, why is he/she here? redirect back to login screen.
            return redirect('/')->withErrors(["email" => trans("otp::messages.unauthorized")]);;
        }
    }

    /**
     * Checks the given OTP
     *
     * @param Request $request
     * @return void
     */
    public function check(Request $request)
    {
        // if user has been logged in
        if ($this->auth->check()) {

            // get the user for querying the verification code
            $user = $this->auth->user();

            // check if current request has a verification code
            if ($request->has("code")) {

                // get the code entered by the user to check
                $code = $request->input("code");

                // get the waiting verification code from database
                $otp = OneTimePassword::where([
                    "user_id" => $user->id,
                    "status" => "waiting",
                ])->orderByDesc("created_at")->first();

                // if the code exists
                if ($otp instanceof OneTimePassword) {
                    // compare it with the received code
                    if ($otp->checkPassword($code)) {

                        // the codes match, set a cookie to expire in one year
                        setcookie("otp_login_verified", "user_id_" . $user->id, time() + (365 * 24 * 60 * 60), "/", "", false, true);

                        // set the code status to "verified" in the database
                        $otp->acceptEntrance();

                        // redirect user to the login redirect path defined in the application

                        // get the application namespace
                        //$namespace = \Illuminate\Container\Container::getInstance()->getNamespace();

                        // check if the stock login controller exists
                        
                        $class = "Modules\\User\\Http\\AuthController";
                        if (class_exists($class)) {

                            // create a new instance of this class to get the redirect path
                            $authenticator = new $class();
                            // redirect to the redirect after login page
                            return redirect($authenticator->redirectPath());
                        } else {
                            //redirect to the root page
                            return redirect()->intended(route(config('asgard.user.config.redirect_route_after_login')));
                        }
                    } else {
                        $failed = setting('otp::otp_max_failed');
                        $otp_max_failed = $failed ? $failed : 3;
                        $otpLog = $otp->oneTimePasswordLogs()->where("status", "waiting")->first();
                        $max_failed = !empty($otpLog) ? $otpLog->otp_max_failed + 1 : 1;
                        $otpLog->update(["otp_max_failed" => $max_failed]);

                        $rest_max_failed = $otp_max_failed > $max_failed ? $otp_max_failed - $max_failed : 0;
                        // the codes don't match, return an error.
                        if ($rest_max_failed) {
                            return redirect(route("otp.view"))->withErrors(["code" => trans("otp::messages.code_mismatch")."You are left {$rest_max_failed} times."]);
                        } else {
                            $otp->discardOldPasswords();
                            $this->auth->logout();
                            return redirect(route("login"))->withErrors(["phone" => trans("otp::messages.otp_max_failed")]);
                        }
                    }
                } else {
                    // the code doesn't exist in the database, return an error.
                    $this->auth->logout();
                    return redirect(route("login"))->withErrors(["phone" => trans("otp::messages.otp_expired")]);
                }
            } else {

                // the code is missing, what should we compare to?
                return redirect(route("otp.view"))->withErrors(["code" => trans("otp::messages.code_missing")]);
            }
        } else {

            // why are you here? we don't have anything to serve to you.
            return redirect(route("login"));
        }
    }
}

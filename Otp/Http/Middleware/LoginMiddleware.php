<?php

namespace Modules\Otp\Http\Middleware;

use Closure;
use Modules\Otp\Entities\OneTimePassword;

class LoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check if the request should be bypassed, or the request doesn't have authentication required
        if ($this->bypassing() ) {// || in_array("auth", $request->route()->computedMiddleware) == false) {
            return $next($request);
        }

        // get the current route
        $routeName = $request->route()->getName();
        // check if the requested route should be checked against OTP verification status
        // and also for the user login status
        // this is needed for skipping the OTP and login routes

        if ($this->willCheck($routeName)) {
            // get the logged in user
            $user = auth()->user();
            if (!$user->allow_otp) {
                return $next($request);
            }
            
            // check for user OTP request in the database
            $otp = $this->getUserOTP($user);
            // define the flag for refreshing the OTP verification code
            $needsRefresh = false;

            // a record exists for the user in the database
            if ($otp instanceof OneTimePassword) {
                // if has a pending OTP verification request
                if ($otp->status == "waiting") {

                    // check timeout
                    if ($otp->isExpired()) {
                        // expired. expire the cookie if exists
                        $this->createExpiredCookie();

                        //  redirect to login page
                        return $this->logout($otp);
                    } else {
                        // still valid. redirect to login verify screen
                        return redirect(route("otp.view"));
                    }
                } else if ($otp->status == "verified") {
                    // verified request. go forth.
                    $response = $next($request);
                    if ($response->status() == 419) {

                        // timeout occured
                        // expire the cookie if exists
                        $this->createExpiredCookie();

                        // redirect to login screen
                        return $this->logout($otp);
                    } else {
                        // create a cookie that will expire in one year
                        $this->createCookie($user->id);

                        // continue to next request
                        return $response;
                    }
                } else {
                    // invalid status, needs to login again.
                    // expire the cookie if exists
                    $this->createExpiredCookie();

                    // redirect to login page
                    return $this->logout($otp);
                }
            } else {
                // creating a new OTP login session
                $otp = OneTimePassword::create([
                    "user_id" => $user->id,
                    "status" => "waiting",
                ]);
                // send the OTP to the user
                if ($otp->send() == true) {
                    // redirect to OTP verification screen
                    return redirect(route('otp.view'));
                } else {
                    // otp send failed, expire the cookie if exists
                    $this->createExpiredCookie();

                    // send the user to login screen with error
                    return $this->logout($otp)->withErrors(trans("otp::messages.service_not_responding"));
                }
            }
        } else {
            // if an active session doesn't exist, but a cookie is present
            if (\Auth::guest() && $this->hasCookie()) {
                // get the user ID from cookie
                $user_id = $this->getUserIdFromCookie();

                // delete the OTP requests from database for that specific user
                OneTimePassword::whereUserId($user_id)->delete();

                // expire that cookie
                $this->createExpiredCookie();
            }
        }

        // continue processing next request.

        return $next($request);
    }

    /**
     * Check if the service should bypass the checks
     *
     * @return boolean
     */
    private function bypassing()
    {
        return \Session::has("otp_service_bypass") && \Session::get("otp_service_bypass", false);
    }

    /**
     * Check if the given route should continue the OTP check
     *
     * @param string $routeName
     * @return boolean
     */
    private function willCheck($routeName)
    {
        return \Auth::check() && config("otp.otp_service_enabled", false) && !in_array($routeName, ['otp.view', 'otp.verify', 'logout']);
    }

    /**
     * Get the active OTP for the given user
     *
     * @param App\User $user
     * @return \tpaksu\LaravelOTPLogin\OneTimePassword
     */
    private function getUserOTP($user)
    {
        return OneTimePassword::whereUserId($user->id)->where("status", "!=", "discarded")->first();
    }

    /**
     * Logs out the user with clearing the OTP records
     *
     * @param \tpaksu\LaravelOTPLogin\OneTimePassword $otp
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    private function logout($otp)
    {
        $otp->discardOldPasswords();
        \Auth::logout();
        return redirect('/');
    }

    /**
     * Checks if the cookie exists with an user id
     *
     * @return boolean
     */
    private function hasCookie()
    {
        return isset($_COOKIE["otp_login_verified"]) && starts_with($_COOKIE["otp_login_verified"], 'user_id_');
    }

    /**
     * Sets the cookie with the user ID inside, active for one year
     *
     * @param \App\User $user_id
     * @return void
     */
    private function createCookie($user_id)
    {
        setcookie("otp_login_verified", "user_id_" . $user_id, time() + (365 * 24 * 60 * 60));
    }

    /**
     * Expires the OTP login verified cookie
     *
     * @return void
     */
    private function createExpiredCookie()
    {
        if ($this->hasCookie()) {
            setcookie("otp_login_verified", "", time() - 100);
        }
    }

    /**
     * Gets the user ID from the OTP login verified cookie
     *
     * @return integer
     */
    private function getUserIdFromCookie()
    {
        return intval(str_replace("user_id_", "", $_COOKIE["otp_login_verified"]));
    }
}

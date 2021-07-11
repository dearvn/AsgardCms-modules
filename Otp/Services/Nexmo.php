<?php

namespace Modules\Otp\Services;

use Modules\User\Entities\Sentinel\User;
use Illuminate\Support\Facades\Log;

/**
 * Nexmo SMS service handler
 *
 */
class Nexmo implements ServiceInterface
{
    /**
     * API key given by nexmo
     *
     * @var string
     */
    private $api_key;

    /**
     * API Secret given by nexmo
     *
     * @var string
     */
    private $api_secret;

    /**
     * The message to be send to the user
     *
     * @var [type]
     */
    private $message;

    /**
     * The User model's phone field name to be used for sending the SMS
     *
     * @var string
     */
    private $phone_column;

    /**
     * FROM number given by nexmo
     *
     * @var string
     */
    private $from;

    /**
     * constructor
     */
    public function __construct()
    {
        $otp_message = setting("otp::otp_message");
        $message = $otp_message ? $otp_message : trans('otp::messages.otp_message');
        
        $this->from = config('otp.services.nexmo.from', "");
        $this->api_key = config('otp.services.nexmo.api_key', "");
        $this->api_secret = config('otp.services.nexmo.api_secret', "");
        $this->message = $message;
        $this->phone_column = config('otp.user_phone_field');
    }

    /**
     * Sends the generated password to the user and returns if it's successful
     *
     * @param App\User $user
     * @param string $otp
     * @param string $ref
     * @return boolean
     */
    public function sendOneTimePassword(User $user, $otp, $ref)
    {
        // extract the phone from the user
        $user_phone = data_get($user, $this->phone_column, false);

        // if the phone isn't set, return false
        if (!$user_phone) return false;

        $user_phone = preg_replace('/[^0-9.]+/', '', $user_phone);
        if ($user_phone[0] == '0') {
            $user_phone = substr($user_phone, 1, strlen($user_phone));
        }
        if (strlen($user_phone) < 11) {
            $user_phone = "1".$user_phone;
        }

        try {
            // prepare the request url
            $url = 'https://rest.nexmo.com/sms/json?' . http_build_query([
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'to' => $user_phone,
                'from' => $this->from,
                'text' => iconv("UTF-8", "ASCII//TRANSLIT", $this->message.": ".$otp)
            ]);

            // prepare the CURL channel
            $ch = curl_init($url);

            // should return the transfer
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // execute the request
            $response = curl_exec($ch);

            // check if response contains the succeeded flag
            if (strpos($response, "\"status\": \"0\",") !== false) {
                return true;
            } else {
                Log::info($response);
                return false;
            }
        } catch (\Exception $e) {

            // return false if any exception occurs
            return false;
        }
    }
}

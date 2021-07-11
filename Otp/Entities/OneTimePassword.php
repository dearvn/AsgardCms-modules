<?php

namespace Modules\Otp\Entities;

use Modules\User\Entities\Sentinel\User;
use Modules\Core\Entities\BaseModel as Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Modules\Otp\Services\ServiceFactory;
use Modules\Otp\Jobs\SendNotificationOtpEmailJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OneTimePassword extends Model
{
    use DispatchesJobs;

    protected $table = 'otp__passwords';
    
    protected $fillable = ["user_id", "status"];

    public function oneTimePasswordLogs()
    {
        return $this->hasMany(OneTimePasswordLog::class, "user_id", "user_id");
    }

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
    
    public function send()
    {
        $ref = random_otp();
        $otp = $this->createOTP($ref);
        if (!empty($otp)) {
            if (config("otp.otp_service_enabled", false)) {
                $data = [
                    'email' => $this->user->email,
                    'message' => setting('otp::otp_message'),
                    'otp' => $otp
                ];
                if ($this->user->otp_method == 'SMS') {
                    return $this->sendOTPWithService($this->user, $otp, $ref);
                } elseif ($this->user->otp_method == 'SmsAndEmail') {
                    $this->dispatch(new SendNotificationOtpEmailJob($data));
                    $this->sendOTPWithService($this->user, $otp, $ref);
                    return true;
                } else {
                    $this->dispatch(new SendNotificationOtpEmailJob($data));
                    return true;
                }
            }
            return true;
        }

        return null;
    }

    private function sendOTPWithService($user, $otp, $ref)
    {
        $OTPFactory = new ServiceFactory();

        $service = $OTPFactory->getService(config("otp.otp_default_service", null));

        if ($service) {
            return $service->sendOneTimePassword($user, $otp, $ref);
        }

        return false;
    }

    public function createOTP($ref)
    {
        $this->discardOldPasswords();
        $otp = random_otp();

        $otp_code = $otp;

        if (config("otp.encode_password", false)) {
            $otp_code = Hash::make($otp);
        }

        $this->update(["status" => "waiting"]);

        $this->oneTimePasswordLogs()->create([
            'user_id' => $this->user->id,
            'otp_code' => $otp_code,
            'refer_number' => $ref,
            'status' => 'waiting',
            'otp_max_failed' => 0
        ]);
        
        return $otp;
    }

    public function discardOldPasswords()
    {
        $this->update(["status" => "discarded"]);
        return $this->oneTimePasswordLogs()->whereIn("status", ["waiting", "verified"])->update(["status" => "discarded"]);

    }

    public function checkPassword($oneTimePassword)
    {
        $oneTimePasswordLog = $this->oneTimePasswordLogs()
            ->where("status", "waiting")->first();

        if (!empty($oneTimePasswordLog)) {

            if (config("otp.encode_password", false)) {
                return Hash::check($oneTimePassword, $oneTimePasswordLog->otp_code);
            } else {
                return $oneTimePasswordLog->otp_code == $oneTimePassword;
            }
        }

        return false;
    }

    public function acceptEntrance()
    {
        $this->update(["status" => "verified"]);
        $this->oneTimePasswordLogs()->where("status", "discarded")->delete();
        OneTimePassword::where(["status" => "discarded", "user_id" => $this->user->id])->delete();
        return $this->oneTimePasswordLogs()->where("user_id", $this->user->id)->where("status", "waiting")->update(["status" => "verified"]);
    }

    public function isExpired()
    {
        $otp_timeout = setting('otp::otp_expired_in');
        $timeout = $otp_timeout ? $otp_timeout * 60 : config("otp.otp_timeout");
        return $this->created_at < Carbon::now()->subSeconds($timeout);
    }
}

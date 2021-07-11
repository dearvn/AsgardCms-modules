<?php

namespace Modules\Otp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Mail\Mailer;
use Modules\Otp\Emails\NotificationOtpEmail;

class SendNotificationOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->data['email'];
        if (empty($email)) {
            return;
        }
        $emails = explode(",", str_replace(";", ",", $email));
        app(Mailer::class)->to($emails)->send(new NotificationOtpEmail($this->data));
    }
}

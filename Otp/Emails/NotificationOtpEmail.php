<?php

namespace Modules\Otp\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationOtpEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var $data
     */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('otp::emails.otp-notification')
            ->subject(trans('otp::messages.one_time_password'));
    }
}

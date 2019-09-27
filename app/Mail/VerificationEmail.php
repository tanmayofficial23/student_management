<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $msg;
    public $url;
    public $buttonText;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg, $url, $buttonText)
    {
        $this->msg = $msg;
        $this->url = $url;

        $this->buttonText = $buttonText;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email');
    }

    
}

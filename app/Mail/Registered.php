<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class Registered extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $appName;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->appName = config('app.name');
        $this->email = $user->email;
        $this->subject = 'Welcome to ' . $this->appName . '!';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.auth.Registered');
    }
}

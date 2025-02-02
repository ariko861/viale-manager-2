<?php

namespace App\Mail;

use App\Models\AutoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoMailSender extends Mailable
{
    use Queueable, SerializesModels;

    public AutoMail $autoMail;

    /**
     * Create a new message instance.
     */
    public function __construct(AutoMail $autoMail)
    {
        $this->autoMail = $autoMail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->autoMail->sujet,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.auto-mail',
            with: ['body' => $this->autoMail->body]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

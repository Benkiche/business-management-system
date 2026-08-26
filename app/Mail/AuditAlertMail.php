<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected array $auditData)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Suspicious Activity Detected',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.audit-alert',
            with: [
                'data' => $this->auditData,
            ],
        );
    }
}
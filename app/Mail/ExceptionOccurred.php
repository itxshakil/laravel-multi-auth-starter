<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ExceptionOccurred extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                '%s [%s] Exception: %s',
                config('app.name'),
                $this->data['environment'] ?? app()->environment(),
                class_basename((string) ($this->data['exception'] ?? 'Throwable')),
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exception-occurred',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LockerAccessed extends Mailable
{
    use Queueable, SerializesModels;

    public $uid;
    public $waktu;

    /**
     * Create a new message instance.
     */
    public function __construct($uid, $waktu)
    {
        $this->uid = $uid;
        $this->waktu = $waktu;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peringatan: Smart Loker Telah Diakses',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.locker_accessed',
            with: [
                'uid' => $this->uid,
                'waktu' => $this->waktu,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

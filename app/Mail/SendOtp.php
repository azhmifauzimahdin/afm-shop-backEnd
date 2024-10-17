<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        switch ($this->mailData['type']) {
            case 'activation':
                return new Envelope(
                    subject: 'Aktivasi Akun AFM Shop',
                );
                break;
            case 'resetPassword':
                return new Envelope(
                    subject: 'Kode Verifikasi untuk Atur Ulang Kata Sandi',
                );
                break;
            case 'verification':
                return new Envelope(
                    subject: 'Kode Verifikasi',
                );
                break;
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.sendOTP',
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

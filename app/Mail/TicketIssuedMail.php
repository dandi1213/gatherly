<?php

namespace App\Mail;

use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class TicketIssuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration) {}

    public function envelope(): Envelope
    {
        $this->registration->loadMissing('event');

        return new Envelope(
            subject: 'Tiket Anda untuk '.$this->registration->event->title,
        );
    }

    public function content(): Content
    {
        $registration = $this->registration->loadMissing([
            'event',
            'ticketType',
            'ticket',
        ]);

        $qrPng = app(QrCodeService::class)->png($registration->ticket->ticket_code, 360);

        // QR dilampirkan inline sebagai gambar (cid:ticket-qr.png@gatherly).
        $this->withSymfonyMessage(function (Email $email) use ($qrPng) {
            $part = (new DataPart($qrPng, 'ticket-qr.png', 'image/png'))
                ->asInline()
                ->setContentId('ticket-qr.png@gatherly');

            $email->addPart($part);
        });

        return new Content(
            view: 'emails.ticket-issued',
            with: [
                'registration' => $registration,
                'ticketUrl' => route('tickets.show', $registration->ticket),
            ],
        );
    }
}

<?php

namespace Tests\Feature;

use App\Mail\TicketIssuedMail;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TicketMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_email_is_sent_with_inline_qr_attachment(): void
    {
        $user = User::factory()->attendee()->create();
        $event = Event::factory()->published()->create([
            'title' => 'Email Test Event',
            'registration_start_at' => now()->subDay(),
            'registration_end_at' => now()->addDay(),
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(3),
        ]);
        $ticketType = TicketType::factory()->create(['event_id' => $event->id, 'quota' => 5]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'registration_number' => 'GTH-2601-MAIL01',
            'attendee_name' => $user->name,
            'attendee_email' => $user->email,
            'status' => 'registered',
        ]);
        Ticket::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_code' => bin2hex(random_bytes(32)),
            'status' => 'active',
        ]);

        Mail::to($registration->attendee_email)->send(new TicketIssuedMail($registration));

        $transport = Mail::mailer()->getSymfonyTransport();
        $this->assertInstanceOf(ArrayTransport::class, $transport);

        $messages = $transport->messages();
        $this->assertCount(1, $messages);

        $email = $messages[0]->getOriginalMessage();
        $this->assertStringContainsString('Email Test Event', $email->getSubject());
        $this->assertStringContainsString('cid:ticket-qr.png@gatherly', $email->getHtmlBody());
        $this->assertStringContainsString('GTH-2601-MAIL01', $email->getHtmlBody());

        $attachments = $email->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertSame('ticket-qr.png@gatherly', $attachments[0]->getContentId());
        $this->assertSame('inline', $attachments[0]->getPreparedHeaders()->getHeaderBody('Content-Disposition'));
        $this->assertSame('image/png', $attachments[0]->getContentType());
    }
}

<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paiement échoué — Votre abonnement InfraSouveraine',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-failed',
            with: [
                'tenant' => $this->tenant,
                'user' => $this->tenant->user,
            ],
        );
    }
}

<?php

namespace App\Mail;

use App\Models\Pod;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeWithCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Pod $pod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your AI Server is Ready — Login Credentials Inside',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.welcome-with-credentials',
            with: [
                'pod' => $this->pod,
                'tenant' => $this->pod->tenant,
                'user' => $this->pod->tenant->user,
                'loginUrl' => $this->buildLoginUrl(),
                'modelName' => config("runpod.recommended_models.{$this->pod->model_id}.display", $this->pod->model_id),
                'gpuName' => config("runpod.gpu_tiers.{$this->pod->gpu_tier}.display", $this->pod->gpu_tier),
                'adminUsername' => $this->pod->decryptedUsername(),
                'adminPassword' => $this->pod->decryptedPassword(),
            ],
        );
    }

    private function buildLoginUrl(): string
    {
        if ($this->pod->webui_url) {
            return $this->pod->webui_url;
        }

        if ($this->pod->public_ip && $this->pod->port) {
            return "http://{$this->pod->public_ip}:{$this->pod->port}";
        }

        return '#';
    }
}

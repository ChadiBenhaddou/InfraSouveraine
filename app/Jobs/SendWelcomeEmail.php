<?php

namespace App\Jobs;

use App\Models\Pod;
use App\Mail\WelcomeWithCredentials;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 30;

    public function __construct(
        public readonly Pod $pod,
    ) {}

    public function handle(): void
    {
        $tenant = $this->pod->tenant;
        $user = $tenant->user;

        if (!$user->email) {
            Log::error('Cannot send welcome email: user has no email', [
                'pod_id' => $this->pod->id,
                'user_id' => $user->id,
            ]);
            return;
        }

        try {
            Mail::to($user->email)
                ->queue(new WelcomeWithCredentials($this->pod));

            Log::info('Welcome email sent', [
                'pod_id' => $this->pod->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send welcome email', [
                'pod_id' => $this->pod->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

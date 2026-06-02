<?php

namespace App\Models;

use App\Enums\PodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pod extends Model
{
    protected $fillable = [
        'tenant_id',
        'runpod_pod_id',
        'status',
        'gpu_tier',
        'model_id',
        'webui_url',
        'internal_ip',
        'public_ip',
        'port',
        'admin_username',
        'admin_password',
        'container_id',
        'runtime_metrics',
        'cost_incurred',
        'provisioned_at',
        'last_active_at',
    ];

    protected $casts = [
        'runtime_metrics' => 'array',
        'cost_incurred' => 'decimal:2',
        'provisioned_at' => 'datetime',
        'last_active_at' => 'datetime',
        'status' => PodStatus::class,
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isRunning(): bool
    {
        return $this->status === PodStatus::RUNNING;
    }

    public function credentialUrl(): ?string
    {
        if (!$this->webui_url || !$this->admin_username || !$this->admin_password) {
            return null;
        }

        $parsed = parse_url($this->webui_url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? $this->public_ip;

        return "{$scheme}://{$this->admin_username}:{$this->admin_password}@{$host}";
    }
}

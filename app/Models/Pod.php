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

    public function decryptedUsername(): ?string
    {
        if (!$this->admin_username) return null;
        try {
            return decrypt($this->admin_username);
        } catch (\Throwable) {
            return $this->admin_username;
        }
    }

    public function decryptedPassword(): ?string
    {
        if (!$this->admin_password) return null;
        try {
            return decrypt($this->admin_password);
        } catch (\Throwable) {
            return $this->admin_password;
        }
    }
}

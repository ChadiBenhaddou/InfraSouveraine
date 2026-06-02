<?php

namespace App\Enums;

enum GpuTier: string
{
    case RTX_4090 = 'RTX_4090';
    case RTX_A6000 = 'RTX_A6000';
    case A100_40GB = 'A100_40GB';
    case A100_80GB = 'A100_80GB';
    case H100 = 'H100';

    public function displayName(): string
    {
        return match ($this) {
            self::RTX_4090 => 'NVIDIA RTX 4090',
            self::RTX_A6000 => 'NVIDIA RTX A6000',
            self::A100_40GB => 'NVIDIA A100 40GB',
            self::A100_80GB => 'NVIDIA A100 80GB',
            self::H100 => 'NVIDIA H100 80GB',
        };
    }

    public function vramGb(): int
    {
        return match ($this) {
            self::RTX_4090 => 24,
            self::RTX_A6000 => 48,
            self::A100_40GB => 40,
            self::A100_80GB => 80,
            self::H100 => 80,
        };
    }

    public function tpsEstimate(): int
    {
        return match ($this) {
            self::RTX_4090 => 85,
            self::RTX_A6000 => 110,
            self::A100_40GB => 180,
            self::A100_80GB => 230,
            self::H100 => 350,
        };
    }

    public function hourlyRate(): float
    {
        return match ($this) {
            self::RTX_4090 => 0.79,
            self::RTX_A6000 => 1.49,
            self::A100_40GB => 2.99,
            self::A100_80GB => 4.49,
            self::H100 => 7.99,
        };
    }

    public function runpodId(): string
    {
        return match ($this) {
            self::RTX_4090 => 'NVIDIA GeForce RTX 4090',
            self::RTX_A6000 => 'NVIDIA RTX A6000',
            self::A100_40GB => 'NVIDIA A100 40GB',
            self::A100_80GB => 'NVIDIA A100 80GB',
            self::H100 => 'NVIDIA H100 80GB',
        };
    }

    public function performanceVsGpt4(): float
    {
        return match ($this) {
            self::RTX_4090 => 0.65,
            self::RTX_A6000 => 0.80,
            self::A100_40GB => 1.10,
            self::A100_80GB => 1.25,
            self::H100 => 1.80,
        };
    }

    public function performanceVsClaude35(): float
    {
        return match ($this) {
            self::RTX_4090 => 0.60,
            self::RTX_A6000 => 0.75,
            self::A100_40GB => 1.05,
            self::A100_80GB => 1.20,
            self::H100 => 1.75,
        };
    }
}

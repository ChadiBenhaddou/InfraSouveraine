<?php

namespace App\Enums;

enum PodStatus: string
{
    case CREATING = 'CREATING';
    case INITIALIZING = 'INITIALIZING';
    case RUNNING = 'RUNNING';
    case STOPPED = 'STOPPED';
    case TERMINATED = 'TERMINATED';
    case FAILED = 'FAILED';
    case UNKNOWN = 'UNKNOWN';
}

<?php

namespace App\Enums;

enum ThreadStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::Closed => 'Cerrada',
            self::Pending => 'Pendiente',
        };
    }
}

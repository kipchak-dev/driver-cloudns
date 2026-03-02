<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Enum;

enum ZoneType: string
{
    case Master  = 'master';
    case Slave   = 'slave';
    case Parked  = 'parked';
    case GeoDns  = 'geodns';
}

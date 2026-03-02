<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Enum;

enum RecordStatus: int
{
    case Active   = 1;
    case Inactive = 0;
}

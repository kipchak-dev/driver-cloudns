<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Result;

use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Enum\ZoneType;

final class ZoneResult
{
    public function __construct(
        public readonly string $name,
        public readonly ZoneType $type,
        public readonly RecordStatus $status,
        /** Short zone name without the TLD */
        public readonly ?string $zone,
        /** IP of the master — populated for slave zones */
        public readonly ?string $masterIp,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:     $data['name'],
            type:     ZoneType::from($data['type']),
            status:   RecordStatus::from((int) ($data['status'] ?? 1)),
            zone:     $data['zone'] ?? null,
            masterIp: $data['master-ip'] ?? null,
        );
    }
}

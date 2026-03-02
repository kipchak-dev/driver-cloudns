<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Result;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Enum\RecordStatus;

final class DnsRecordResult
{
    public function __construct(
        public readonly int $id,
        public readonly DnsRecordType $type,
        /** Subdomain/hostname part; empty string means zone root */
        public readonly string $host,
        /** Record value: IP, hostname, or text depending on type */
        public readonly string $record,
        public readonly int $ttl,
        public readonly RecordStatus $status,
        public readonly ?int $priority,
        public readonly ?int $weight,
        public readonly ?int $port,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:       (int) $data['id'],
            type:     DnsRecordType::from($data['type']),
            host:     (string) ($data['host'] ?? ''),
            record:   (string) ($data['record'] ?? ''),
            ttl:      (int) ($data['ttl'] ?? 3600),
            status:   RecordStatus::from((int) ($data['status'] ?? 1)),
            priority: isset($data['priority']) && $data['priority'] !== '' ? (int) $data['priority'] : null,
            weight:   isset($data['weight'])   && $data['weight']   !== '' ? (int) $data['weight']   : null,
            port:     isset($data['port'])     && $data['port']     !== '' ? (int) $data['port']     : null,
        );
    }
}

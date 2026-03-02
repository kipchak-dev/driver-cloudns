<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Request;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;

final class ListDnsRecordsRequest
{
    /**
     * @param string|null        $host        Filter by exact hostname
     * @param string|null        $hostLike    Filter by partial hostname match
     * @param DnsRecordType|null $type        Filter by record type
     * @param int                $rowsPerPage Must be 10, 20, 30, 50, or 100
     * @param int                $page        Page number (1-based)
     * @param string|null        $orderBy     Sort by 'host' or 'record'
     */
    public function __construct(
        public readonly ?string $host = null,
        public readonly ?string $hostLike = null,
        public readonly ?DnsRecordType $type = null,
        public readonly int $rowsPerPage = 100,
        public readonly int $page = 1,
        public readonly ?string $orderBy = null,
    ) {}

    public function toArray(): array
    {
        $params = [
            'rows-per-page' => $this->rowsPerPage,
            'page'          => $this->page,
        ];

        if ($this->host !== null) {
            $params['host'] = $this->host;
        }
        if ($this->hostLike !== null) {
            $params['host-like'] = $this->hostLike;
        }
        if ($this->type !== null) {
            $params['type'] = $this->type->value;
        }
        if ($this->orderBy !== null) {
            $params['order-by'] = $this->orderBy;
        }

        return $params;
    }
}

<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Request;

use Kipchak\Driver\ClouDns\Enum\RecordStatus;

/**
 * Modifies an existing DNS record. The record type cannot be changed.
 * host and ttl are required; all other fields are optional.
 */
final class ModifyDnsRecordRequest
{
    /**
     * @param string           $host           Subdomain/hostname; use '' or '@' for the zone root
     * @param int              $ttl            TTL in seconds — supported values: 60, 300, 900, 1800,
     *                                         3600, 21600, 43200, 86400, 172800, 259200, 604800,
     *                                         1209600, 2592000
     * @param string|null      $record         New record value — omit to leave unchanged
     * @param int|null         $priority       MX/SRV records — omit to leave unchanged
     * @param int|null         $weight         SRV records only — omit to leave unchanged
     * @param int|null         $port           SRV records only — omit to leave unchanged
     * @param RecordStatus|null $status        Active or Inactive — omit to leave unchanged
     * @param int|null         $caaFlag        CAA records: 0 or 128 — omit to leave unchanged
     * @param string|null      $caaType        CAA records: 'issue', 'issuewild', 'iodef' — omit to leave unchanged
     * @param string|null      $caaValue       CAA records — omit to leave unchanged
     * @param int|null         $geodnsLocation GeoDNS location ID — GeoDNS zones only
     * @param string|null      $geodnsCode     GeoDNS location code — GeoDNS zones only
     */
    public function __construct(
        public readonly string $host,
        public readonly int $ttl,
        public readonly ?string $record = null,
        public readonly ?int $priority = null,
        public readonly ?int $weight = null,
        public readonly ?int $port = null,
        public readonly ?RecordStatus $status = null,
        public readonly ?int $caaFlag = null,
        public readonly ?string $caaType = null,
        public readonly ?string $caaValue = null,
        public readonly ?int $geodnsLocation = null,
        public readonly ?string $geodnsCode = null,
    ) {}

    public function toArray(): array
    {
        $params = [
            'host' => $this->host,
            'ttl'  => $this->ttl,
        ];

        if ($this->record !== null) {
            $params['record'] = $this->record;
        }
        if ($this->priority !== null) {
            $params['priority'] = $this->priority;
        }
        if ($this->weight !== null) {
            $params['weight'] = $this->weight;
        }
        if ($this->port !== null) {
            $params['port'] = $this->port;
        }
        if ($this->status !== null) {
            $params['status'] = $this->status->value;
        }
        if ($this->caaFlag !== null) {
            $params['caa-flag'] = $this->caaFlag;
        }
        if ($this->caaType !== null) {
            $params['caa-type'] = $this->caaType;
        }
        if ($this->caaValue !== null) {
            $params['caa-value'] = $this->caaValue;
        }
        if ($this->geodnsLocation !== null) {
            $params['geodns-location'] = $this->geodnsLocation;
        }
        if ($this->geodnsCode !== null) {
            $params['geodns-code'] = $this->geodnsCode;
        }

        return $params;
    }
}

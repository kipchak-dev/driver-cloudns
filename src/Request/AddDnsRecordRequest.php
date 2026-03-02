<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Request;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Enum\RecordStatus;

final class AddDnsRecordRequest
{
    /**
     * @param DnsRecordType    $type          Record type
     * @param string           $host          Subdomain/hostname; use '' or '@' for the zone root
     * @param int              $ttl           TTL in seconds — supported values: 60, 300, 900, 1800,
     *                                        3600, 21600, 43200, 86400, 172800, 259200, 604800,
     *                                        1209600, 2592000
     * @param string|null      $record        Record value: IP for A/AAAA, hostname for CNAME/MX/NS/SRV,
     *                                        text for TXT/SPF, etc.
     * @param int|null         $priority      Required for MX; used for SRV
     * @param int|null         $weight        SRV records only
     * @param int|null         $port          SRV records only
     * @param RecordStatus     $status        Active (1) or Inactive (0)
     * @param int|null         $caaFlag       CAA records: 0 (non-critical) or 128 (critical)
     * @param string|null      $caaType       CAA records: 'issue', 'issuewild', or 'iodef'
     * @param string|null      $caaValue      CAA records: the CA hostname or iodef URL/email
     * @param int|null         $geodnsLocation GeoDNS location ID
     * @param string|null      $geodnsCode    GeoDNS location code
     */
    public function __construct(
        public readonly DnsRecordType $type,
        public readonly string $host,
        public readonly int $ttl,
        public readonly ?string $record = null,
        public readonly ?int $priority = null,
        public readonly ?int $weight = null,
        public readonly ?int $port = null,
        public readonly RecordStatus $status = RecordStatus::Active,
        public readonly ?int $caaFlag = null,
        public readonly ?string $caaType = null,
        public readonly ?string $caaValue = null,
        public readonly ?int $geodnsLocation = null,
        public readonly ?string $geodnsCode = null,
    ) {}

    public function toArray(): array
    {
        $params = [
            'type'   => $this->type->value,
            'host'   => $this->host,
            'ttl'    => $this->ttl,
            'status' => $this->status->value,
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

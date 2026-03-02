<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Result;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Result\DnsRecordResult;
use PHPUnit\Framework\TestCase;

final class DnsRecordResultTest extends TestCase
{
    public function testFromArrayMapsARecord(): void
    {
        $record = DnsRecordResult::fromArray($this->fixture());

        $this->assertSame(123456, $record->id);
        $this->assertSame(DnsRecordType::A, $record->type);
        $this->assertSame('api', $record->host);
        $this->assertSame('192.0.2.1', $record->record);
        $this->assertSame(3600, $record->ttl);
        $this->assertSame(RecordStatus::Active, $record->status);
        $this->assertNull($record->priority);
        $this->assertNull($record->weight);
        $this->assertNull($record->port);
    }

    public function testFromArrayMapsMxRecordWithPriority(): void
    {
        $record = DnsRecordResult::fromArray(array_merge($this->fixture(), [
            'type'     => 'MX',
            'host'     => '',
            'record'   => 'mail.example.com',
            'priority' => '10',
        ]));

        $this->assertSame(DnsRecordType::MX, $record->type);
        $this->assertSame(10, $record->priority);
    }

    public function testFromArrayMapsSrvRecordWithWeightAndPort(): void
    {
        $record = DnsRecordResult::fromArray(array_merge($this->fixture(), [
            'type'     => 'SRV',
            'host'     => '_sip._tcp',
            'record'   => 'sip.example.com',
            'priority' => '10',
            'weight'   => '20',
            'port'     => '5060',
        ]));

        $this->assertSame(DnsRecordType::SRV, $record->type);
        $this->assertSame(10, $record->priority);
        $this->assertSame(20, $record->weight);
        $this->assertSame(5060, $record->port);
    }

    public function testFromArrayHandlesStringNumericValues(): void
    {
        // ClouDNS returns all values as strings — driver must cast correctly
        $record = DnsRecordResult::fromArray([
            'id'     => '999',
            'type'   => 'TXT',
            'host'   => '',
            'record' => 'v=spf1 ~all',
            'ttl'    => '300',
            'status' => '1',
        ]);

        $this->assertSame(999, $record->id);
        $this->assertSame(300, $record->ttl);
        $this->assertSame(RecordStatus::Active, $record->status);
    }

    public function testFromArrayHandlesEmptyPriorityWeightPort(): void
    {
        $record = DnsRecordResult::fromArray(array_merge($this->fixture(), [
            'priority' => '',
            'weight'   => '',
            'port'     => '',
        ]));

        $this->assertNull($record->priority);
        $this->assertNull($record->weight);
        $this->assertNull($record->port);
    }

    private function fixture(): array
    {
        return [
            'id'     => '123456',
            'type'   => 'A',
            'host'   => 'api',
            'record' => '192.0.2.1',
            'ttl'    => '3600',
            'status' => '1',
        ];
    }
}

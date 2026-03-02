<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Request;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Request\AddDnsRecordRequest;
use PHPUnit\Framework\TestCase;

final class AddDnsRecordRequestTest extends TestCase
{
    public function testRequiredFieldsAlwaysPresent(): void
    {
        $request = new AddDnsRecordRequest(
            type: DnsRecordType::A,
            host: 'api',
            ttl:  3600,
        );

        $data = $request->toArray();

        $this->assertSame('A', $data['type']);
        $this->assertSame('api', $data['host']);
        $this->assertSame(3600, $data['ttl']);
        $this->assertSame(RecordStatus::Active->value, $data['status']);
    }

    public function testOptionalFieldsOmittedWhenNull(): void
    {
        $data = (new AddDnsRecordRequest(
            type: DnsRecordType::TXT,
            host: '',
            ttl:  3600,
        ))->toArray();

        $this->assertArrayNotHasKey('record', $data);
        $this->assertArrayNotHasKey('priority', $data);
        $this->assertArrayNotHasKey('weight', $data);
        $this->assertArrayNotHasKey('port', $data);
        $this->assertArrayNotHasKey('caa-flag', $data);
        $this->assertArrayNotHasKey('caa-type', $data);
        $this->assertArrayNotHasKey('caa-value', $data);
        $this->assertArrayNotHasKey('geodns-location', $data);
        $this->assertArrayNotHasKey('geodns-code', $data);
    }

    public function testMxRecordIncludesPriority(): void
    {
        $data = (new AddDnsRecordRequest(
            type:     DnsRecordType::MX,
            host:     '',
            ttl:      3600,
            record:   'mail.example.com',
            priority: 10,
        ))->toArray();

        $this->assertSame('MX', $data['type']);
        $this->assertSame('mail.example.com', $data['record']);
        $this->assertSame(10, $data['priority']);
    }

    public function testSrvRecordIncludesWeightAndPort(): void
    {
        $data = (new AddDnsRecordRequest(
            type:     DnsRecordType::SRV,
            host:     '_sip._tcp',
            ttl:      3600,
            record:   'sip.example.com',
            priority: 10,
            weight:   20,
            port:     5060,
        ))->toArray();

        $this->assertSame(20, $data['weight']);
        $this->assertSame(5060, $data['port']);
    }

    public function testCaaRecordIncludesCaaFields(): void
    {
        $data = (new AddDnsRecordRequest(
            type:     DnsRecordType::CAA,
            host:     '',
            ttl:      3600,
            caaFlag:  0,
            caaType:  'issue',
            caaValue: 'letsencrypt.org',
        ))->toArray();

        $this->assertSame(0, $data['caa-flag']);
        $this->assertSame('issue', $data['caa-type']);
        $this->assertSame('letsencrypt.org', $data['caa-value']);
    }

    public function testInactiveStatusIsSerialised(): void
    {
        $data = (new AddDnsRecordRequest(
            type:   DnsRecordType::A,
            host:   'test',
            ttl:    300,
            status: RecordStatus::Inactive,
        ))->toArray();

        $this->assertSame(0, $data['status']);
    }
}

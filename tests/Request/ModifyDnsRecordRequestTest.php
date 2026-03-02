<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Request;

use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Request\ModifyDnsRecordRequest;
use PHPUnit\Framework\TestCase;

final class ModifyDnsRecordRequestTest extends TestCase
{
    public function testRequiredFieldsAlwaysPresent(): void
    {
        $data = (new ModifyDnsRecordRequest(host: 'api', ttl: 3600))->toArray();

        $this->assertSame('api', $data['host']);
        $this->assertSame(3600, $data['ttl']);
    }

    public function testOptionalFieldsOmittedWhenNull(): void
    {
        $data = (new ModifyDnsRecordRequest(host: 'api', ttl: 3600))->toArray();

        $this->assertArrayNotHasKey('record', $data);
        $this->assertArrayNotHasKey('priority', $data);
        $this->assertArrayNotHasKey('weight', $data);
        $this->assertArrayNotHasKey('port', $data);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertArrayNotHasKey('caa-flag', $data);
        $this->assertArrayNotHasKey('caa-type', $data);
        $this->assertArrayNotHasKey('caa-value', $data);
        $this->assertArrayNotHasKey('geodns-location', $data);
        $this->assertArrayNotHasKey('geodns-code', $data);
    }

    public function testGeoDnsFieldsIncludedWhenProvided(): void
    {
        $data = (new ModifyDnsRecordRequest(
            host:           '@',
            ttl:            300,
            record:         '192.0.2.1',
            geodnsLocation: 7,
            geodnsCode:     'EU',
        ))->toArray();

        $this->assertSame(7, $data['geodns-location']);
        $this->assertSame('EU', $data['geodns-code']);
    }

    public function testAllOptionalFieldsIncludedWhenProvided(): void
    {
        $data = (new ModifyDnsRecordRequest(
            host:     'mail',
            ttl:      1800,
            record:   'mail.example.com',
            priority: 20,
            weight:   10,
            port:     25,
            status:   RecordStatus::Inactive,
            caaFlag:  128,
            caaType:  'issuewild',
            caaValue: 'letsencrypt.org',
        ))->toArray();

        $this->assertSame('mail.example.com', $data['record']);
        $this->assertSame(20, $data['priority']);
        $this->assertSame(10, $data['weight']);
        $this->assertSame(25, $data['port']);
        $this->assertSame(0, $data['status']);
        $this->assertSame(128, $data['caa-flag']);
        $this->assertSame('issuewild', $data['caa-type']);
        $this->assertSame('letsencrypt.org', $data['caa-value']);
    }
}

<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Library;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Library\ClouDnsClient;
use Kipchak\Driver\ClouDns\Request\AddDnsRecordRequest;
use Kipchak\Driver\ClouDns\Request\ListDnsRecordsRequest;
use Kipchak\Driver\ClouDns\Request\ListZonesRequest;
use Kipchak\Driver\ClouDns\Request\ModifyDnsRecordRequest;
use Kipchak\Driver\ClouDns\Result\DnsRecordResult;
use Kipchak\Driver\ClouDns\Result\ZoneResult;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ClouDnsClientTest extends TestCase
{
    private const DOMAIN = 'example.com';
    private const REC_ID = 123456;

    // -------------------------------------------------------------------------
    // Zones
    // -------------------------------------------------------------------------

    public function testListZonesReturnsZoneResultArray(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response(
                $this->listZonesResponse(), 200
            ),
        ]);

        $results = $this->client($http)->listZones();

        $this->assertCount(1, $results);
        $this->assertInstanceOf(ZoneResult::class, $results[0]);
        $this->assertSame(self::DOMAIN, $results[0]->name);
    }

    public function testListZonesReturnsEmptyArrayWhenNoZones(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response([], 200),
        ]);

        $this->assertSame([], $this->client($http)->listZones());
    }

    public function testListZonesForwardsSearchParam(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response(
                $this->listZonesResponse(), 200
            ),
        ]);

        $this->client($http)->listZones(new ListZonesRequest(search: 'example'));

        $http->assertSent(function (Request $request) {
            return $request->data()['search'] === 'example';
        });
    }

    public function testGetZoneInfoReturnsZoneResult(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/get-zone-info.json' => Factory::response(
                $this->zoneFixture(), 200
            ),
        ]);

        $zone = $this->client($http)->getZoneInfo(self::DOMAIN);

        $this->assertInstanceOf(ZoneResult::class, $zone);
        $this->assertSame(self::DOMAIN, $zone->name);
    }

    public function testGetZonePageCountReturnsInteger(): void
    {
        $http = $this->fake([
            // ClouDNS answers this endpoint with a bare JSON number, so the fake body
            // must be the raw string — the HTTP fake no longer accepts a scalar int.
            'https://api.cloudns.net/dns/get-pages-count.json' => Factory::response('5', 200),
        ]);

        $this->assertSame(5, $this->client($http)->getZonePageCount(10));
    }

    // -------------------------------------------------------------------------
    // DNS Records
    // -------------------------------------------------------------------------

    public function testListRecordsReturnsDnsRecordResultArray(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/records.json' => Factory::response(
                $this->listRecordsResponse(), 200
            ),
        ]);

        $results = $this->client($http)->listRecords(self::DOMAIN);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(DnsRecordResult::class, $results[0]);
        $this->assertSame(self::REC_ID, $results[0]->id);
    }

    public function testListRecordsReturnsEmptyArrayWhenNoRecords(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/records.json' => Factory::response([], 200),
        ]);

        $this->assertSame([], $this->client($http)->listRecords(self::DOMAIN));
    }

    public function testListRecordsForwardsDomainNameAndFilters(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/records.json' => Factory::response(
                $this->listRecordsResponse(), 200
            ),
        ]);

        $this->client($http)->listRecords(self::DOMAIN, new ListDnsRecordsRequest(
            type: DnsRecordType::A,
            host: 'api',
        ));

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $data['domain-name'] === self::DOMAIN
                && $data['type'] === 'A'
                && $data['host'] === 'api';
        });
    }

    public function testGetRecordReturnsDnsRecordResult(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/get-record.json' => Factory::response(
                $this->recordFixture(), 200
            ),
        ]);

        $record = $this->client($http)->getRecord(self::DOMAIN, self::REC_ID);

        $this->assertInstanceOf(DnsRecordResult::class, $record);
        $this->assertSame(self::REC_ID, $record->id);
        $this->assertSame(DnsRecordType::A, $record->type);
    }

    public function testAddRecordSendsCorrectPayload(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/add-record.json' => Factory::response(
                ['status' => 'Success', 'statusDescription' => 'The record was added successfully.'], 200
            ),
        ]);

        $this->client($http)->addRecord(self::DOMAIN, new AddDnsRecordRequest(
            type:   DnsRecordType::A,
            host:   'api',
            ttl:    3600,
            record: '192.0.2.1',
        ));

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $data['domain-name'] === self::DOMAIN
                && $data['type'] === 'A'
                && $data['host'] === 'api'
                && $data['record'] === '192.0.2.1'
                && (int) $data['ttl'] === 3600;
        });
    }

    public function testModifyRecordSendsCorrectPayload(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/mod-record.json' => Factory::response(
                ['status' => 'Success', 'statusDescription' => 'The record was modified successfully.'], 200
            ),
        ]);

        $this->client($http)->modifyRecord(self::DOMAIN, self::REC_ID, new ModifyDnsRecordRequest(
            host:   'api',
            ttl:    300,
            record: '192.0.2.2',
        ));

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $data['domain-name'] === self::DOMAIN
                && (int) $data['record-id'] === self::REC_ID
                && $data['record'] === '192.0.2.2'
                && (int) $data['ttl'] === 300;
        });
    }

    public function testDeleteRecordSendsCorrectPayload(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/delete-record.json' => Factory::response(
                ['status' => 'Success', 'statusDescription' => 'The record was deleted successfully.'], 200
            ),
        ]);

        $this->client($http)->deleteRecord(self::DOMAIN, self::REC_ID);

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $data['domain-name'] === self::DOMAIN
                && (int) $data['record-id'] === self::REC_ID;
        });
    }

    // -------------------------------------------------------------------------
    // Auth params
    // -------------------------------------------------------------------------

    public function testAuthIdIsSentWithEveryRequest(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response([], 200),
        ]);

        $this->client($http)->listZones();

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return (int) $data['auth-id'] === 42
                && $data['auth-password'] === 'secret';
        });
    }

    public function testSubAuthUserIsSentWhenConfigured(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response([], 200),
        ]);

        $client = new ClouDnsClient(
            ['sub_auth_user' => 'subuser', 'auth_password' => 'secret'],
            $http,
        );
        $client->listZones();

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return isset($data['sub-auth-user'])
                && $data['sub-auth-user'] === 'subuser'
                && !isset($data['auth-id']);
        });
    }

    public function testSubAuthIdIsSentWhenConfigured(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response([], 200),
        ]);

        $client = new ClouDnsClient(
            ['sub_auth_id' => 99, 'auth_password' => 'secret'],
            $http,
        );
        $client->listZones();

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return isset($data['sub-auth-id'])
                && (int) $data['sub-auth-id'] === 99
                && !isset($data['auth-id']);
        });
    }

    // -------------------------------------------------------------------------
    // Error handling
    // -------------------------------------------------------------------------

    public function testFailedResponseThrowsRuntimeException(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/list-zones.json' => Factory::response([
                'status'            => 'Failed',
                'statusDescription' => 'Invalid authentication.',
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid authentication/');

        $this->client($http)->listZones();
    }

    public function testErrorMessageIncludesClouDnsPrefix(): void
    {
        $http = $this->fake([
            'https://api.cloudns.net/dns/records.json' => Factory::response([
                'status'            => 'Failed',
                'statusDescription' => 'Zone not found.',
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/\[ClouDNS\]/');

        $this->client($http)->listRecords(self::DOMAIN);
    }

    // -------------------------------------------------------------------------
    // Constructor validation
    // -------------------------------------------------------------------------

    public function testMissingAuthPasswordThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/auth_password/');

        new ClouDnsClient(['auth_id' => 1, 'auth_password' => ''], new Factory());
    }

    public function testMissingAuthIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/auth_id/');

        new ClouDnsClient(['auth_password' => 'secret'], new Factory());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function client(Factory $http): ClouDnsClient
    {
        return new ClouDnsClient(['auth_id' => 42, 'auth_password' => 'secret'], $http);
    }

    private function fake(array $responses): Factory
    {
        $http = new Factory();
        $http->fake($responses);
        return $http;
    }

    private function zoneFixture(): array
    {
        return [
            'name'   => self::DOMAIN,
            'type'   => 'master',
            'zone'   => 'example',
            'status' => '1',
        ];
    }

    private function listZonesResponse(): array
    {
        return [self::DOMAIN => $this->zoneFixture()];
    }

    private function recordFixture(): array
    {
        return [
            'id'     => (string) self::REC_ID,
            'type'   => 'A',
            'host'   => 'api',
            'record' => '192.0.2.1',
            'ttl'    => '3600',
            'status' => '1',
        ];
    }

    private function listRecordsResponse(): array
    {
        return [(string) self::REC_ID => $this->recordFixture()];
    }
}

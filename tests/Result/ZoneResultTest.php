<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Result;

use Kipchak\Driver\ClouDns\Enum\RecordStatus;
use Kipchak\Driver\ClouDns\Enum\ZoneType;
use Kipchak\Driver\ClouDns\Result\ZoneResult;
use PHPUnit\Framework\TestCase;

final class ZoneResultTest extends TestCase
{
    public function testFromArrayMapsMasterZone(): void
    {
        $zone = ZoneResult::fromArray([
            'name'   => 'example.com',
            'type'   => 'master',
            'zone'   => 'example',
            'status' => '1',
        ]);

        $this->assertSame('example.com', $zone->name);
        $this->assertSame(ZoneType::Master, $zone->type);
        $this->assertSame(RecordStatus::Active, $zone->status);
        $this->assertSame('example', $zone->zone);
        $this->assertNull($zone->masterIp);
    }

    public function testFromArrayMapsSlaveZoneWithMasterIp(): void
    {
        $zone = ZoneResult::fromArray([
            'name'      => 'example.net',
            'type'      => 'slave',
            'zone'      => 'example',
            'status'    => '1',
            'master-ip' => '192.0.2.1',
        ]);

        $this->assertSame(ZoneType::Slave, $zone->type);
        $this->assertSame('192.0.2.1', $zone->masterIp);
    }

    public function testFromArrayHandlesInactiveStatus(): void
    {
        $zone = ZoneResult::fromArray([
            'name'   => 'example.org',
            'type'   => 'master',
            'status' => '0',
        ]);

        $this->assertSame(RecordStatus::Inactive, $zone->status);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $zone = ZoneResult::fromArray([
            'name' => 'example.io',
            'type' => 'parked',
        ]);

        $this->assertSame(ZoneType::Parked, $zone->type);
        $this->assertSame(RecordStatus::Active, $zone->status);
        $this->assertNull($zone->zone);
        $this->assertNull($zone->masterIp);
    }
}

<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Request;

use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Request\ListDnsRecordsRequest;
use PHPUnit\Framework\TestCase;

final class ListDnsRecordsRequestTest extends TestCase
{
    public function testDefaultsAreIncluded(): void
    {
        $data = (new ListDnsRecordsRequest())->toArray();

        $this->assertSame(100, $data['rows-per-page']);
        $this->assertSame(1, $data['page']);
    }

    public function testNullFieldsAreOmitted(): void
    {
        $data = (new ListDnsRecordsRequest())->toArray();

        $this->assertArrayNotHasKey('host', $data);
        $this->assertArrayNotHasKey('host-like', $data);
        $this->assertArrayNotHasKey('type', $data);
        $this->assertArrayNotHasKey('order-by', $data);
    }

    public function testAllFiltersIncludedWhenSet(): void
    {
        $data = (new ListDnsRecordsRequest(
            host:        'www',
            hostLike:    'api',
            type:        DnsRecordType::A,
            rowsPerPage: 20,
            page:        2,
            orderBy:     'host',
        ))->toArray();

        $this->assertSame('www', $data['host']);
        $this->assertSame('api', $data['host-like']);
        $this->assertSame('A', $data['type']);
        $this->assertSame(20, $data['rows-per-page']);
        $this->assertSame(2, $data['page']);
        $this->assertSame('host', $data['order-by']);
    }
}

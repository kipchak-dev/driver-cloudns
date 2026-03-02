<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests\Request;

use Kipchak\Driver\ClouDns\Request\ListZonesRequest;
use PHPUnit\Framework\TestCase;

final class ListZonesRequestTest extends TestCase
{
    public function testDefaultsAreIncluded(): void
    {
        $data = (new ListZonesRequest())->toArray();

        $this->assertSame(1, $data['page']);
        $this->assertSame(10, $data['rows-per-page']);
    }

    public function testNullFieldsAreOmitted(): void
    {
        $data = (new ListZonesRequest())->toArray();

        $this->assertArrayNotHasKey('search', $data);
        $this->assertArrayNotHasKey('group-id', $data);
    }

    public function testOptionalFieldsIncludedWhenSet(): void
    {
        $data = (new ListZonesRequest(
            page:        3,
            rowsPerPage: 50,
            search:      'example.com',
            groupId:     7,
        ))->toArray();

        $this->assertSame(3, $data['page']);
        $this->assertSame(50, $data['rows-per-page']);
        $this->assertSame('example.com', $data['search']);
        $this->assertSame(7, $data['group-id']);
    }
}

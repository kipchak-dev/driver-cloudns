# Kipchak ClouDNS Driver

A fully typed [ClouDNS](https://www.cloudns.net/) DNS driver for the
[Kipchak API Development Kit](https://kipchak.dev). Manage DNS zones and records using the
ClouDNS API directly — no SDK dependency required.

## Requirements

- PHP 8.4+
- [Kipchak](https://kipchak.dev) ^2
- `kipchak/driver-config` ^1
- `kipchak/driver-http` ^1

## Installation

```bash
composer require kipchak/driver-cloudns
```

### Initialise

Add to your `drivers/drivers.php`:

```php
\Kipchak\Driver\ClouDns\ClouDns::initialise($container);
```

## Configuration

Create `config/kipchak.cloudns.php`:

```php
<?php

use function Kipchak\Core\env;

return [
    'connections' => [
        'default' => [
            'auth_id'       => env('CLOUDNS_AUTH_ID', ''),
            'auth_password' => env('CLOUDNS_AUTH_PASSWORD', ''),
        ],
    ],
];
```

Supply exactly one identity key per connection: `auth_id`, `sub_auth_id`, or `sub_auth_user`.
`auth_password` is always required.

## Usage

```php
use Kipchak\Driver\ClouDns\ClouDns;
use Kipchak\Driver\ClouDns\Enum\DnsRecordType;
use Kipchak\Driver\ClouDns\Request\AddDnsRecordRequest;
use Kipchak\Driver\ClouDns\Request\ModifyDnsRecordRequest;

$dns = ClouDns::get(); // 'default' connection

// List zones
$zones = $dns->listZones();

// List records
$records = $dns->listRecords('example.com');

// Add an A record
$dns->addRecord('example.com', new AddDnsRecordRequest(
    type:   DnsRecordType::A,
    host:   'api',
    ttl:    3600,
    record: '192.0.2.1',
));

// Modify a record
$dns->modifyRecord('example.com', 123456, new ModifyDnsRecordRequest(
    host:   'api',
    ttl:    300,
    record: '192.0.2.2',
));

// Delete a record
$dns->deleteRecord('example.com', 123456);
```

### GeoDNS

Add GeoDNS-targeted records using the `geodnsLocation` and `geodnsCode` parameters:

```php
use Kipchak\Driver\ClouDns\Request\AddDnsRecordRequest;
use Kipchak\Driver\ClouDns\Enum\DnsRecordType;

// Add a GeoDNS A record targeting European visitors
$dns->addRecord('example.com', new AddDnsRecordRequest(
    type:           DnsRecordType::A,
    host:           '@',
    ttl:            60,
    record:         '198.51.100.10',
    geodnsLocation: 7,      // ClouDNS location ID for Europe
    geodnsCode:     'EU',
));

// Modify an existing GeoDNS record
$dns->modifyRecord('example.com', 654321, new ModifyDnsRecordRequest(
    host:           '@',
    ttl:            60,
    record:         '198.51.100.20',
    geodnsLocation: 7,
    geodnsCode:     'EU',
));
```

GeoDNS requires a zone of type `ZoneType::GeoDns`. Location IDs and codes are available
from the ClouDNS control panel or the `list-geodns-locations` API endpoint.

## Features

- Multiple named connections (main account, sub-accounts, sub-users)
- Zone management: list zones, get zone info, page count
- DNS record management: list, get, add, modify, delete
- Typed enums for record types (`DnsRecordType`), zone types (`ZoneType`), and status (`RecordStatus`)
- Typed request objects: `AddDnsRecordRequest`, `ModifyDnsRecordRequest`, `ListZonesRequest`, `ListDnsRecordsRequest`
- Typed result objects: `ZoneResult`, `DnsRecordResult`
- Full record-type support: A, AAAA, ALIAS, CAA, CERT, CNAME, DNSKEY, DS, HINFO, HTTPS, LOC, MX, NAPTR, NS, PTR, RP, SMIMEA, SPF, SRV, SSHFP, SVCB, TLSA, TXT, WR
- GeoDNS record support

## Running Tests

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT — see [LICENSE](LICENSE).

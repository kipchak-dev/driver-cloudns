<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns;

use Kipchak\Core\Interfaces;
use Kipchak\Driver\ClouDns\Library\ClouDnsClient;
use Kipchak\Driver\Config\Config;
use Kipchak\Driver\Http\Library\Http as HttpClient;
use Psr\Container\ContainerInterface;

class ClouDns implements Interfaces\Driver
{
    protected static ?ContainerInterface $container = null;

    public static function initialise(ContainerInterface $container, ?string $configFileName = null): void
    {
        self::$container = $container;
        self::set($configFileName);
    }

    public static function set(?string $configFileName = null): void
    {
        $configFileName = $configFileName ?? 'kipchak.cloudns';
        $config = Config::get($configFileName);

        if (empty($config['connections'])) {
            return;
        }

        foreach ($config['connections'] as $connectionName => $connectionDetails) {
            self::$container->set(
                'drivers.dns.cloudns.' . $connectionName,
                function (ContainerInterface $c) use ($connectionName, $connectionDetails): ClouDnsClient {
                    $baseUrl = $connectionDetails['base_url'] ?? 'https://api.cloudns.net';

                    return new ClouDnsClient($connectionDetails, new HttpClient(), $baseUrl);
                }
            );
        }
    }

    public static function get(?string $configKey = null): ClouDnsClient
    {
        return self::$container->get('drivers.dns.cloudns.' . ($configKey ?? 'default'));
    }
}

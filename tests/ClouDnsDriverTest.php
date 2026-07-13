<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Tests;

use Kipchak\Driver\ClouDns\ClouDns;
use Kipchak\Driver\ClouDns\Library\ClouDnsClient;
use Kipchak\Driver\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

final class ClouDnsDriverTest extends TestCase
{
    public function testRegistersOneContainerEntryPerConnection(): void
    {
        $container = $this->primeDriver([
            'connections' => [
                'default'   => ['auth_id' => 1, 'auth_password' => 'secret'],
                'secondary' => ['auth_id' => 2, 'auth_password' => 'secret2'],
            ],
        ]);

        $this->assertTrue($container->has('drivers.dns.cloudns.default'));
        $this->assertTrue($container->has('drivers.dns.cloudns.secondary'));
    }

    public function testGetReturnsClouDnsClientInstance(): void
    {
        $this->primeDriver([
            'connections' => [
                'default' => ['auth_id' => 1, 'auth_password' => 'secret'],
            ],
        ]);

        $this->assertInstanceOf(ClouDnsClient::class, ClouDns::get());
        $this->assertInstanceOf(ClouDnsClient::class, ClouDns::get('default'));
    }

    public function testEmptyConnectionsDoesNotRegisterAnyEntries(): void
    {
        $container = $this->primeDriver([]);

        $this->assertFalse($container->has('drivers.dns.cloudns.default'));
    }

    public function testMissingAuthPasswordThrowsOnResolve(): void
    {
        $this->primeDriver([
            'connections' => [
                'default' => ['auth_id' => 1, 'auth_password' => ''],
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        ClouDns::get('default');
    }

    public function testMissingCredentialsThrowsOnResolve(): void
    {
        $this->primeDriver([
            'connections' => [
                'default' => ['auth_password' => 'secret'],
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        ClouDns::get('default');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function primeDriver(array $config): TestContainer
    {
        $configContainer = new TestContainer();
        $configContainer->set('drivers.config', ['kipchak.cloudns' => $config]);
        $this->setStaticProperty(Config::class, 'container', $configContainer);

        $driverContainer = new TestContainer();
        ClouDns::initialise($driverContainer);

        return $driverContainer;
    }

    private function setStaticProperty(string $class, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($class);
        $prop = $reflection->getProperty($property);
        $prop->setValue(null, $value);
    }
}

// ---------------------------------------------------------------------------
// Test doubles
// ---------------------------------------------------------------------------

final class TestContainer implements ContainerInterface
{
    private array $entries = [];

    public function set(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new TestNotFoundException('Service not found: ' . $id);
        }

        $entry = $this->entries[$id];

        return is_callable($entry) ? $entry($this) : $entry;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }
}

final class TestNotFoundException extends \RuntimeException implements NotFoundExceptionInterface {}

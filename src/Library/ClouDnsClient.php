<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Library;

use Illuminate\Http\Client\Factory;
use InvalidArgumentException;
use Kipchak\Driver\ClouDns\Request\AddDnsRecordRequest;
use Kipchak\Driver\ClouDns\Request\ListDnsRecordsRequest;
use Kipchak\Driver\ClouDns\Request\ListZonesRequest;
use Kipchak\Driver\ClouDns\Request\ModifyDnsRecordRequest;
use Kipchak\Driver\ClouDns\Result\DnsRecordResult;
use Kipchak\Driver\ClouDns\Result\ZoneResult;
use RuntimeException;

final class ClouDnsClient
{
    private readonly string $baseUrl;
    private readonly array $authParams;

    /**
     * @param array{
     *   auth_id?: int|string,
     *   sub_auth_id?: int|string,
     *   sub_auth_user?: string,
     *   auth_password: string
     * } $credentials
     */
    public function __construct(
        array $credentials,
        private readonly Factory $http,
        string $baseUrl = 'https://api.cloudns.net',
    ) {
        $this->baseUrl    = rtrim($baseUrl, '/');
        $this->authParams = $this->buildAuthParams($credentials);
    }

    // -------------------------------------------------------------------------
    // Zones
    // -------------------------------------------------------------------------

    /**
     * List zones, optionally filtered and paginated.
     *
     * @return ZoneResult[]
     */
    public function listZones(ListZonesRequest $request = new ListZonesRequest()): array
    {
        $body = $this->request('/dns/list-zones.json', $request->toArray());

        if (!is_array($body) || empty($body)) {
            return [];
        }

        $zones = [];
        foreach ($body as $item) {
            if (is_array($item) && isset($item['name'])) {
                $zones[] = ZoneResult::fromArray($item);
            }
        }

        return $zones;
    }

    /**
     * Get information for a single zone.
     */
    public function getZoneInfo(string $domainName): ZoneResult
    {
        $body = $this->request('/dns/get-zone-info.json', ['domain-name' => $domainName]);

        return ZoneResult::fromArray((array) $body);
    }

    /**
     * Get the total number of pages for the zones list.
     *
     * @param int         $rowsPerPage Must be 10, 20, 30, 50, 100, or 250
     * @param string|null $search      Optional domain name filter
     */
    public function getZonePageCount(int $rowsPerPage = 10, ?string $search = null): int
    {
        $params = ['rows-per-page' => $rowsPerPage];

        if ($search !== null) {
            $params['search'] = $search;
        }

        return (int) $this->request('/dns/get-pages-count.json', $params);
    }

    // -------------------------------------------------------------------------
    // DNS Records
    // -------------------------------------------------------------------------

    /**
     * List DNS records for a zone, optionally filtered.
     *
     * @return DnsRecordResult[]
     */
    public function listRecords(string $domainName, ListDnsRecordsRequest $request = new ListDnsRecordsRequest()): array
    {
        $body = $this->request('/dns/records.json', array_merge(
            ['domain-name' => $domainName],
            $request->toArray(),
        ));

        if (!is_array($body) || empty($body)) {
            return [];
        }

        $records = [];
        foreach ($body as $item) {
            if (is_array($item) && isset($item['id'])) {
                $records[] = DnsRecordResult::fromArray($item);
            }
        }

        return $records;
    }

    /**
     * Get a single DNS record by ID.
     */
    public function getRecord(string $domainName, int $recordId): DnsRecordResult
    {
        $body = $this->request('/dns/get-record.json', [
            'domain-name' => $domainName,
            'record-id'   => $recordId,
        ]);

        return DnsRecordResult::fromArray((array) $body);
    }

    /**
     * Add a new DNS record. Throws RuntimeException on failure.
     */
    public function addRecord(string $domainName, AddDnsRecordRequest $request): void
    {
        $this->request('/dns/add-record.json', array_merge(
            ['domain-name' => $domainName],
            $request->toArray(),
        ));
    }

    /**
     * Modify an existing DNS record. The record type cannot be changed.
     * Throws RuntimeException on failure.
     */
    public function modifyRecord(string $domainName, int $recordId, ModifyDnsRecordRequest $request): void
    {
        $this->request('/dns/mod-record.json', array_merge(
            ['domain-name' => $domainName, 'record-id' => $recordId],
            $request->toArray(),
        ));
    }

    /**
     * Delete a DNS record. Throws RuntimeException on failure.
     */
    public function deleteRecord(string $domainName, int $recordId): void
    {
        $this->request('/dns/delete-record.json', [
            'domain-name' => $domainName,
            'record-id'   => $recordId,
        ]);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function request(string $endpoint, array $params = []): mixed
    {
        $response = $this->http
            ->asForm()
            ->acceptJson()
            ->post($this->baseUrl . $endpoint, array_merge($this->authParams, $params));

        $body = $response->json();

        if (is_array($body) && isset($body['status']) && strtolower((string) $body['status']) === 'failed') {
            throw new RuntimeException(
                '[ClouDNS] ' . ($body['statusDescription'] ?? 'Request failed') . ' (HTTP ' . $response->status() . ')'
            );
        }

        return $body;
    }

    private function buildAuthParams(array $credentials): array
    {
        $password = $credentials['auth_password'] ?? null;

        if ($password === null || $password === '') {
            throw new InvalidArgumentException('ClouDNS connection requires auth_password.');
        }

        $params = ['auth-password' => $password];

        if (!empty($credentials['sub_auth_user'])) {
            $params['sub-auth-user'] = (string) $credentials['sub_auth_user'];
        } elseif (!empty($credentials['sub_auth_id'])) {
            $params['sub-auth-id'] = (int) $credentials['sub_auth_id'];
        } elseif (!empty($credentials['auth_id'])) {
            $params['auth-id'] = (int) $credentials['auth_id'];
        } else {
            throw new InvalidArgumentException('ClouDNS connection requires auth_id, sub_auth_id, or sub_auth_user.');
        }

        return $params;
    }
}

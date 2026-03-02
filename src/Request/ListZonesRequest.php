<?php

declare(strict_types=1);

namespace Kipchak\Driver\ClouDns\Request;

final class ListZonesRequest
{
    /**
     * @param int      $page        Page number (1-based)
     * @param int      $rowsPerPage Results per page — must be 10, 20, 30, 50, or 100
     * @param string|null $search   Filter by domain name or keyword
     * @param int|null $groupId     Limit to a specific group
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $rowsPerPage = 10,
        public readonly ?string $search = null,
        public readonly ?int $groupId = null,
    ) {}

    public function toArray(): array
    {
        $params = [
            'page'         => $this->page,
            'rows-per-page' => $this->rowsPerPage,
        ];

        if ($this->search !== null) {
            $params['search'] = $this->search;
        }
        if ($this->groupId !== null) {
            $params['group-id'] = $this->groupId;
        }

        return $params;
    }
}

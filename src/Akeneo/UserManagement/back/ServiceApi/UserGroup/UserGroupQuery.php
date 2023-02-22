<?php

namespace Akeneo\UserManagement\ServiceApi\UserGroup;

class UserGroupQuery
{
    public const DEFAULT_LIMIT = 1000;

    // @todo add arguments to allow filter on label and pagination
    // @todo validate the pagination consistency
    public function __construct(
        private ?string $searchName = null,
        private ?int $searchAfterId = null,
        private int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public function getSearchName(): ?string
    {
        return $this->searchName;
    }

    public function getSearchAfterId(): ?int
    {
        return $this->searchAfterId;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}

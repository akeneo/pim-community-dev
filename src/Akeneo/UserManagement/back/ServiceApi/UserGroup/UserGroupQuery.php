<?php

namespace Akeneo\UserManagement\ServiceApi\UserGroup;

class UserGroupQuery
{
    public function __construct(
        private ?string $searchName = null,
        private ?int $searchAfterId = null,
        private ?int $limit = null,
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

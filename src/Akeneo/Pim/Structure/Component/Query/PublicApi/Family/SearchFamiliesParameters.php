<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

class SearchFamiliesParameters
{
    public function __construct(
        private ?array $includeCodes = null,
        private ?array $excludeCodes = null,
        private ?string $search = null,
        private ?string $searchLanguage = null,
        private ?int $limit = null,
        private ?int $page = null,
    ) {
    }

    public function getIncludeCodes(): ?array
    {
        return $this->includeCodes;
    }

    public function getExcludeCodes(): ?array
    {
        return $this->excludeCodes;
    }

    public function setIncludeCodes(?array $includeCodes): void
    {
        $this->includeCodes = $includeCodes;
    }

    public function setExcludeCodes(?array $excludeCodes): void
    {
        $this->excludeCodes = $excludeCodes;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getSearchLanguage(): ?string
    {
        return $this->searchLanguage;
    }

    public function setSearchLanguage(?string $searchLanguage): void
    {
        $this->searchLanguage = $searchLanguage;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    public function getOffset(): ?int
    {
        if (null === $this->page || null === $this->limit) {
            return null;
        }

        return ($this->page - 1) * $this->limit;
    }
}

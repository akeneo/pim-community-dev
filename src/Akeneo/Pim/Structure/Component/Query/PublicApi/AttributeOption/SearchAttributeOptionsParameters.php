<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAttributeOptionsParameters
{
    /** @var string[] | null */
    private ?array $includeCodes = null;

    /** @var string[] | null */
    private ?array $excludeCodes = null;

    private ?string $search = null;
    private ?string $locale = null;
    private ?int $limit = null;
    private ?int $page = null;

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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
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

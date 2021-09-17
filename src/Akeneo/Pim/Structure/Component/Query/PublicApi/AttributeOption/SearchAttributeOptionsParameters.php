<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAttributeOptionsParameters
{
    private ?string $attributeCode;
    private ?array $attributeOptionCodes;
    private ?string $search;
    private ?string $locale;
    private ?string $catalogLocale;
    private ?int $limit;
    private ?int $page;

    public function getAttributeCode(): ?string
    {
        return $this->attributeCode;
    }

    public function setAttributeCode(?string $attributeCode): void
    {
        $this->attributeCode = $attributeCode;
    }

    public function getAttributeOptionCodes(): ?array
    {
        return $this->attributeOptionCodes;
    }

    public function setAttributeOptionCodes(?array $attributeOptionCodes): void
    {
        $this->attributeOptionCodes = $attributeOptionCodes;
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

    public function getCatalogLocale(): ?string
    {
        return $this->catalogLocale;
    }

    public function setCatalogLocale(?string $catalogLocale): void
    {
        $this->catalogLocale = $catalogLocale;
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
}

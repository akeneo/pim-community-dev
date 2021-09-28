<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform;

class SearchRecordsParameters
{
    /** @var string[] | null */
    private ?array $includeCodes = null;

    /** @var string[] | null */
    private ?array $excludeCodes = null;

    private ?string $search = null;
    private int $limit = 25;
    private int $page = 1;

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

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }
}

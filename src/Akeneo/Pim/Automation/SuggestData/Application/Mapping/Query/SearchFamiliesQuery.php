<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesQuery
{
    /** @var int */
    private $limit;

    /** @var int */
    private $page;

    /** @var array|null */
    private $familyIdentifiers;

    /** @var string */
    private $search;

    /**
     * @param int $limit
     * @param int $page
     * @param array|null $familyIdentifiers
     * @param null|string $search
     */
    public function __construct(int $limit, int $page, array $familyIdentifiers, ?string $search)
    {
        $this->limit = $limit;
        $this->page = $page;
        $this->familyIdentifiers = $familyIdentifiers;
        $this->search = $search;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return array|null
     */
    public function getFamilyIdentifiers(): ?array
    {
        return $this->familyIdentifiers;
    }

    /**
     * @return string
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }
}

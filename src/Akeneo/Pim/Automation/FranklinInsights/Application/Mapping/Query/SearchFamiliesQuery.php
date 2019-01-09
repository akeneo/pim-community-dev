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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesQuery
{
    /** @var int */
    private $limit;

    /** @var int */
    private $page;

    /** @var string */
    private $search;

    /**
     * @param int $limit
     * @param int $page
     * @param null|string $search
     */
    public function __construct(int $limit, int $page, ?string $search)
    {
        $this->limit = $limit;
        $this->page = $page;
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
     * @return string
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }
}

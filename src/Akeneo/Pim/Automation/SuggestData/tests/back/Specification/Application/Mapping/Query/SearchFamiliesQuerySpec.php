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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(10, 2, 'router');
    }

    public function it_is_a_get_families_query(): void
    {
        $this->shouldHaveType(SearchFamiliesQuery::class);
    }

    public function it_gets_the_limit(): void
    {
        $this->getLimit()->shouldReturn(10);
    }

    public function it_gets_the_page(): void
    {
        $this->getPage()->shouldReturn(2);
    }

    public function it_gets_the_search_query(): void
    {
        $this->getSearch()->shouldReturn('router');
    }
}

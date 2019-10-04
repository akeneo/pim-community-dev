<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class DraftSourceFilterSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['draft_source'], ['IN']);
    }

    public function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    public function it_adds_a_filter_on_the_draft_source(SearchQueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);

        $queryBuilder->addFilter([
            'terms' => [
                'source' => ['pim', 'franklin'],
            ],
        ])->shouldBeCalled();

        $this->addFieldFilter('draft_source', 'IN', ['pim', 'franklin']);
    }

    public function it_throws_an_exception_if_the_query_builder_is_not_initialized()
    {
        $this->shouldThrow(new \LogicException('The search query builder is not initialized in the filter.'))
            ->during('addFieldFilter', ['draft_source', 'IN', ['pim', 'franklin']]);
    }

    public function it_throws_an_exception_if_the_filter_value_is_not_an_array(SearchQueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('addFieldFilter', ['draft_source', 'IN', 'pim']);
    }
}

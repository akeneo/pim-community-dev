<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ProductIdFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_adds_a_in_filter_on_product_ids_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('_id')->willReturn($queryBuilder);
        $queryBuilder->in(['hash1', 'hash2'])->willReturn($queryBuilder);

        $this->addFieldFilter('id', 'IN', ['hash1', 'hash2']);
    }

    function it_throws_an_exception_if_value_is_not_a_numeric_or_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::expected('id', 'array or string value', 'filter', 'productId', 1234))
            ->during('addFieldFilter', ['id', '=', 1234]);
    }
}

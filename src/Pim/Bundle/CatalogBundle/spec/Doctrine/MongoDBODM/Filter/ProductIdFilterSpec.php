<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ProductIdFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['id'], ['=', 'IN', 'NOT IN', '!=']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_adds_a_in_filter_on_product_ids_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('_id')->willReturn($queryBuilder);
        $queryBuilder->in(['hash1', 'hash2'])->willReturn($queryBuilder);

        $this->addFieldFilter('id', 'IN', ['hash1', 'hash2']);
    }

    function it_throws_an_exception_if_value_is_not_a_numeric_or_an_array()
    {
        $this->shouldThrow(
            new InvalidPropertyTypeException(
                'id',
                1234,
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\ProductIdFilter',
                'Property "id" expects array or string value, "integer" given.',
                100
            )
        )
            ->during('addFieldFilter', ['id', '=', 1234]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['id']);
    }
}

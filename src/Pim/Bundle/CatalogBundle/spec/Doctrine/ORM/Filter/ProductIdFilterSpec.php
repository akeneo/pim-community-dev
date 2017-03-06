<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class ProductIdFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['id'], ['=']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['id']);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_binary_filter_in_the_query(QueryBuilder $queryBuilder)
    {
        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAliases()->willReturn(['p']);
        $queryBuilder->andWhere('p.id = \'12\'')->shouldBeCalled();

        $this->addFieldFilter('id', '=', '12');
    }

    function it_throws_an_exception_if_value_is_not_a_numeric_or_an_array()
    {
        $this->shouldThrow(
            new InvalidPropertyTypeException(
                'id',
                'WRONG',
                'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\ProductIdFilter',
                'Property "id" expects array or numeric value, "string" given.',
                100
            )
        )->during('addFieldFilter', ['id', '=', 'WRONG']);
    }
}

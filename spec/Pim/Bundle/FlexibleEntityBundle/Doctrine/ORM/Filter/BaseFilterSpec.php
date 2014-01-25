<?php

namespace spec\Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

class BaseFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FlexibleEntityBundle\Doctrine\FilterInterface');
    }

    function it_adds_a_like_filter_in_the_query(QueryBuilder $queryBuilder, Attribute $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->getBackendStorage()->willReturn('values');
        $sku->isTranslatable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku1.attribute = 42 AND filtersku1.varchar LIKE 'My Sku'";

        $queryBuilder->innerJoin('p.values', 'filtersku1', 'WITH', $condition)->shouldBeCalled();

        $this->add($sku, 'LIKE', 'My Sku');
    }
}

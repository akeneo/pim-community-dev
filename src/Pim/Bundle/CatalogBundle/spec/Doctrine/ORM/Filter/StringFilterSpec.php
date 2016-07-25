<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class StringFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(
            ['pim_catalog_identifier'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'ENDS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            'IN',
            'EMPTY',
            'NOT EMPTY',
            '!='
        ]);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_contains_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_does_not_contain_attribute_filter_in_the_query(
        QueryBuilder $queryBuilder,
        AttributeInterface $sku
    ) {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_equal_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, '=', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_not_empty_attribute_filter_in_the_query(
        QueryBuilder $queryBuilder,
        AttributeInterface $sku,
        Expr $expr,
        Expr\Literal $literal
    ) {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->getRootAlias()->willReturn('p');

        $expr->literal('')->shouldBeCalled()->willReturn($literal);
        $expr->isNotNull(Argument::type('string'))->shouldBeCalled();
        $expr->neq(Argument::type('string'), $literal)->shouldBeCalled();

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'NOT EMPTY', null, null, null, ['field' => 'sku']);
    }

    function it_adds_an_empty_attribute_filter_in_the_query(
        QueryBuilder $queryBuilder,
        AttributeInterface $sku
    ) {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $queryBuilder->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($sku, 'EMPTY', null, null, null, ['field' => 'sku']);
    }

    function it_adds_a_not_equal_attribute_filter_in_the_query(
        QueryBuilder $queryBuilder,
        AttributeInterface $sku,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->getRootAlias()->willReturn('p');
        $expr->literal('My Sku')->willReturn($literal);
        $expr->notLike(Argument::any(), 'My Sku')->shouldBeCalled()->willReturn($comp);
        $literal->__toString()->willReturn('My Sku');
        $comp->__toString()->willReturn('filtersku.varchar NOT LIKE "My Sku"');

        $queryBuilder->innerJoin(
            Argument::any(),
            Argument::any(),
            'WITH',
            Argument::containingString('.attribute = 42 AND filtersku.varchar NOT LIKE "My Sku"')
        )->shouldBeCalled();

        $this->addAttributeFilter($sku, '!=', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string', gettype(123)))
            ->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
    }
}

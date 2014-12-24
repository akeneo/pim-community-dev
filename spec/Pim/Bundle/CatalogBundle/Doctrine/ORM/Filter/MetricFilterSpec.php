<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

class MetricFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['pim_catalog_metric'], ['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_metric_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');
        $attribute->getCode()->willReturn('metric_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin('r.values', 'filtermetric_code', 'WITH', 'filtermetric_code.attribute = 42')->shouldBeCalled();
        $qb
            ->innerJoin(
                'filtermetric_code.metric',
                'filterMmetric_code',
                'WITH',
                'filterMmetric_code.baseData = 16'
            )
            ->shouldBeCalled()
        ;

        $this->addAttributeFilter($attribute, '=', 16);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('metric');
        $attribute->getCode()->willReturn('metric_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin('r.values', 'filtermetric_code', 'WITH', 'filtermetric_code.attribute = 42')->shouldBeCalled();
        $qb->leftJoin('filtermetric_code.metric', 'filterMmetric_code')->shouldBeCalled();
        $qb->andWhere('filterMmetric_code.baseData IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('metric_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('metric_code', 'filter', 'metric'))
            ->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}

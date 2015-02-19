<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

class MetricSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\MetricSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\AttributeSorterInterface');
    }

    function it_supports_metric_attribute(AttributeInterface $metric)
    {
        $metric->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($metric)->shouldReturn(true);

        $metric->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($metric)->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb, AttributeInterface $metric)
    {
        $metric->getId()->willReturn(42);
        $metric->getCode()->willReturn('metric_code');
        $metric->getBackendType()->willReturn('metric');
        $metric->isLocalizable()->willReturn(false);
        $metric->isScopable()->willReturn(false);

        $condition = "sorterVmetric_code.attribute = 42";
        $qb->getRootAlias()->willReturn('r');

        $qb->leftJoin('r.values', 'sorterVmetric_code', 'WITH', $condition)->shouldBeCalled();
        $qb->leftJoin('sorterVmetric_code.metric', 'sorterMmetric_code')->shouldBeCalled();
        $qb->addOrderBy('sorterMmetric_code.baseData', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addAttributeSorter($metric, 'DESC');
    }
}

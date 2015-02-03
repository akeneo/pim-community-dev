<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InGroupSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\InGroupSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_the_in_group_field()
    {
        $this->supportsField('in_group_ok')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb)
    {
        $inGroupExpr = 'CASE WHEN :currentGroup MEMBER OF p.groups THEN true ELSE false END';
        $qb->getRootAlias()->willReturn('r');

        $qb->addSelect(sprintf('%s AS %s', $inGroupExpr, 'inGroupSorter'))->shouldBeCalled()->willReturn($qb);
        $qb->addOrderBy('inGroupSorter', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addFieldSorter('in_group_ok', 'DESC');
    }
}

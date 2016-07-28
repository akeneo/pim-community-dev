<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Join;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class ValueJoinSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin');
    }

    function it_prepares_condition_on_localizable_attribute(AttributeInterface $name, Expr $expr, $qb)
    {
        $name->getId()->willReturn(42);
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->prepareCondition($name, 'alias', 'en_US');
    }
}

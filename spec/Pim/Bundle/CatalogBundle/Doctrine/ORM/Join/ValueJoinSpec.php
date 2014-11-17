<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Join;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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

    function it_throws_an_exception_when_the_locale_is_not_provided(AttributeInterface $name)
    {
        $name->getId()->willReturn(42);
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringPrepareCondition($name, 'alias');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringPrepareCondition($name, 'alias', null);
    }

    function it_throws_an_exception_when_the_scope_is_not_provided(AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(true);
        $price->getCode()->willReturn('price');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringPrepareCondition($price, 'alias', 'en_US', null);
    }
}

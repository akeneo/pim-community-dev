<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class IsAssociatedSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\IsAssociatedSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface');
    }

    function it_supports_the_is_associated_field()
    {
        $this->supportsField('is_associated')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb)
    {
        $qb->getRootAlias()->willReturn('r');
        $qb->addOrderBy('is_associated', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addFieldSorter('is_associated', 'DESC');
    }
}

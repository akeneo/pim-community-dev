<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class OptionsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_multi_select_attribute(AbstractAttribute $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, AbstractAttribute $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            'filteroptions_code',
            'WITH',
            'filteroptions_code.attribute = 42'
        )->shouldBeCalled()->willReturn($qb);
        $qb->innerJoin(
            'filteroptions_code.options',
            'filterOoptions_code',
            'WITH',
            'filterOoptions_code.id IN(\'my_value1\', \'my_value2\')'
        )->shouldBeCalled()->willReturn($qb);

        $this->addAttributeFilter($attribute, 'IN', ['my_value1', 'my_value2']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AbstractAttribute $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin(
            'r.values',
            'filteroptions_code',
            'WITH',
            'filteroptions_code.attribute = 42'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin( 'filteroptions_code.options', 'filterOoptions_code')->shouldBeCalled()->willReturn($qb);
        $qb
            ->andWhere(
                'filterOoptions_code.id IS NULL OR filterOoptions_code.id IN(\'my_value2\', \'my_value3\')'
            )
            ->shouldBeCalled()
        ;

        $this->addAttributeFilter($attribute, 'IN', ['empty', 'my_value2', 'my_value3']);
    }
}

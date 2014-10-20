<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class OptionFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
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
        $this->supportsOperator(Argument::any())->shouldReturn(false);
    }

    function it_supports_simple_select_attribute(AbstractAttribute $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, AbstractAttribute $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            'filteroption_code',
            'WITH',
            'filteroption_code.attribute = 42 AND ( filteroption_code.option IN(\'my_value1\', \'my_value2\') ) '
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['my_value1', 'my_value2']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AbstractAttribute $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin(
            'r.values',
            'filteroption_code',
            'WITH',
            'filteroption_code.attribute = 42'
        )->shouldBeCalled();
        $qb->andWhere('filteroption_code.option IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['empty']);
    }

    function it_adds_an_empty_filter_and_another_filter_to_the_query($qb, AbstractAttribute $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb
            ->leftJoin(
                'r.values',
                'filteroption_code',
                'WITH',
                'filteroption_code.attribute = 42'
            )
            ->shouldBeCalled()
        ;
        $qb
            ->andWhere(
                'filteroption_code.option IS NULL OR filteroption_code.option IN(\'my_value2\', \'my_value3\')'
            )
            ->shouldBeCalled()
        ;

        $this->addAttributeFilter($attribute, 'IN', ['empty', 'my_value2', 'my_value3']);
    }
}

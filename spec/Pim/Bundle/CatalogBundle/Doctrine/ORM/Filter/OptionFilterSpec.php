<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\ObjectIdResolverInterface;
use Prophecy\Argument;

class OptionFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith($objectIdResolver, ['pim_catalog_simpleselect'], ['IN', 'EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator(Argument::any())->shouldReturn(false);
    }

    function it_supports_simple_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, AttributeInterface $attribute)
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
            'filteroption_code.attribute = 42 AND ( filteroption_code.option IN(\'1\', \'2\') ) '
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['1', '2'], null, null, ['field' => 'options_code.id']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AttributeInterface $attribute)
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

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::arrayExpected('option_code', 'filter', 'option'))->during('addAttributeFilter', [$attribute, 'IN', 'WRONG', null, null, ['field' => 'option_code.id']]);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('option_code', 'filter', 'option'))
            ->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric'], null, null, ['field' => 'option_code.id']]);
    }
}

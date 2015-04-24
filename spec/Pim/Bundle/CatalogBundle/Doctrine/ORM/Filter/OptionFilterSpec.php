<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class OptionFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $objectIdResolver,
            ['pim_catalog_simpleselect'],
            ['IN', 'EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
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

    function it_adds_a_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['1', '2'], null, null, ['field' => 'options_code.id']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('option_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin(
            'r.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();
        $qb->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null, ['field' => 'options_code.id']);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::arrayExpected('option_code', 'filter', 'option', gettype('WRONG')))->during('addAttributeFilter', [$attribute, 'IN', 'WRONG', null, null, ['field' => 'option_code.id']]);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('option_code', 'filter', 'option', gettype('not numeric')))
            ->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric'], null, null, ['field' => 'option_code.id']]);
    }
}

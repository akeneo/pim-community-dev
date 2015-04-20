<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ReferenceDataFilterSpec extends ObjectBehavior
{
    function let(
        Builder $qb,
        AttributeValidatorHelper $attrValidatorHelper,
        ConfigurationRegistryInterface $registry,
        ReferenceDataIdResolver $idResolver
    ) {
        $this->beConstructedWith($attrValidatorHelper, $registry, $idResolver, ['IN', 'EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query(
        $attrValidatorHelper,
        Builder $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getCode()->willReturn('color');

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.color.id')->shouldBeCalled()->willReturn($expr);
        $expr->in([118, 270])->shouldBeCalled()->willReturn($expr);
        $qb->addAnd($expr)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', [118, 270], null, null, ['field' => 'color']);
    }

    function it_adds_a_filter_with_codes_to_the_query(
        $attrValidatorHelper,
        $idResolver,
        Builder $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getCode()->willReturn('color');
        $attribute->getReferenceDataName()->willReturn('ref_data_color');

        $idResolver->resolve('ref_data_color', ['red', 'blue'])->willReturn([118, 270]);

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.color.id')->shouldBeCalled()->willReturn($expr);
        $expr->in([118, 270])->shouldBeCalled()->willReturn($expr);
        $qb->addAnd($expr)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['red', 'blue'], null, null, ['field' => 'color.code']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, $attrValidatorHelper, AttributeInterface $attribute, Expr $expr)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->getCode()->willReturn('color');

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.color.id')->shouldBeCalled()->willReturn($expr);
        $expr->exists(false)->shouldBeCalled()->willReturn($expr);
        $qb->addAnd($expr)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, null, null);
    }

    function it_throws_an_exception_if_value_is_not_a_valid_array(AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(1);
        $attribute->getCode()->willReturn('color');

        $value = 'string';
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('color', 'filter', 'reference_data', $value)
        )
            ->during('addAttributeFilter', [$attribute, '=', $value, null, null, ['field' => 'color']]);

        $value = ['foo'];
        $this->shouldThrow(
            InvalidArgumentException::numericExpected('color', 'filter', 'reference_data', 'string')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value, null, null, ['field' => 'color']]);
    }
}

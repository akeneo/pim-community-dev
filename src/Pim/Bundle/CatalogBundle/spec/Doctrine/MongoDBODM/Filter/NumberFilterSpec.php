<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class NumberFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_metric'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equals_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->equals(22.5)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', 22.5, 'en_US', 'mobile');
    }

    function it_adds_a_not_equal_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(true)->shouldBeCalled();
        $queryBuilder->notEqual(22.5)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '!=', 22.5, 'en_US', 'mobile');
    }

    function it_adds_an_empty_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null, 'en_US', 'mobile');
    }

    function it_adds_a_not_empty_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'NOT EMPTY', null, 'en_US', 'mobile');
    }

    function it_adds_a_lower_than_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lt(42)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '<', 42, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gt(42)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '>', 42, 'en_US', 'mobile');
    }

    function it_adds_a_lower_than_or_equals_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lte(42)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '<=', 42, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query($attrValidatorHelper, $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gte(42)->shouldBeCalled();

        $this->addAttributeFilter($attribute, '>=', 42, 'en_US', 'mobile');
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('number_code');

        $this->shouldThrow(InvalidArgumentException::numericExpected('number_code', 'filter', 'number', gettype('WRONG')))
            ->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}

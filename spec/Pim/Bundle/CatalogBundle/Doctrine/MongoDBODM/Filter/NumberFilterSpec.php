<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class NumberFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($attrValidatorHelper, ['pim_catalog_metric'], ['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_equals_filter_in_the_query($attrValidatorHelper, Builder $queryBuilder, AttributeInterface $attribute)
    {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile')->willReturn($queryBuilder);
        $queryBuilder->equals('22.5')->willReturn($queryBuilder);

        $this->addAttributeFilter($attribute, '=', '22.5', 'en_US', 'mobile');
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('number_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('number_code', 'filter', 'number', gettype('WRONG')))
            ->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}

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
class DateFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_date'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);

        $queryBuilder->field(Argument::any())->willReturn($queryBuilder);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->equals(strtotime('2014-03-15'))->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, '=', '2014-03-15');
        $this->addAttributeFilter($date, '=', new \DateTime('2014-03-15'));
    }

    function it_adds_a_not_equal_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->exists(true)->shouldBeCalledTimes(2);
        $queryBuilder->notEqual(strtotime('2014-03-15'))->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, '!=', '2014-03-15');
        $this->addAttributeFilter($date, '!=', new \DateTime('2014-03-15'));
    }

    function it_adds_a_less_than_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->lt(strtotime('2014-03-15'))->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, '<', '2014-03-15');
        $this->addAttributeFilter($date, '<', new \DateTime('2014-03-15'));
    }

    function it_adds_a_greater_than_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->gt(strtotime('2014-03-15'))->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, '>', '2014-03-15');
        $this->addAttributeFilter($date, '>', new \DateTime('2014-03-15'));
    }

    function it_adds_an_empty_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($date, 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($date, 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_an_attribute_value_in_the_query(
        $attrValidatorHelper,
        $queryBuilder,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->gte(strtotime('2014-03-15'))->shouldBeCalledTimes(2);
        $queryBuilder->lte(strtotime('2014-03-20'))->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, 'BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addAttributeFilter($date, 'BETWEEN', [new \DateTime('2014-03-15'), new \DateTime('2014-03-20')]);
    }

    function it_adds_a_not_between_filter_on_an_attribute_value_in_the_query(
        $queryBuilder,
        $attrValidatorHelper,
        AttributeInterface $date
    ) {
        $attrValidatorHelper->validateLocale($date, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($date, Argument::any())->shouldBeCalled();

        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);
        $queryBuilder->gt(strtotime('2014-03-20'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);

        $this->addAttributeFilter($date, 'NOT BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addAttributeFilter($date, 'NOT BETWEEN', [new \DateTime('2014-03-15'), new \DateTime('2014-03-20')]);
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_datetime(AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r(123, true)
            )
        )->during('addAttributeFilter', [$date, '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format(AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'a string with the format Y-m-d',
                'filter',
                'date',
                'not a valid date format'
            )
        )->during('addAttributeFilter', [$date, '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates(AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addAttributeFilter', [$date, '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values(AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addAttributeFilter', [$date, '>', [123, 123, 'three']]);
    }
}

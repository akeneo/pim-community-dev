<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class MetricFilterSpec extends ObjectBehavior
{
    function let(
        Builder $queryBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $measureManager,
            $measureConverter,
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

    function it_supports_metric_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_an_equals_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->equals(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '=', $value, 'en_US', 'mobile');
    }

    function it_adds_a_not_equal_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(true)->shouldBeCalled($queryBuilder);
        $queryBuilder->notEqual(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '!=', $value, 'en_US', 'mobile');
    }

    function it_adds_an_empty_filter_in_the_query(
        $attrValidatorHelper,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => null, 'unit' => 'CENTIMETER'];

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);

        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($metric, 'EMPTY', $value, 'en_US', 'mobile');
    }

    function it_adds_a_not_empty_filter_in_the_query(
        $attrValidatorHelper,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => null, 'unit' => 'CENTIMETER'];

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);

        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($metric, 'NOT EMPTY', $value, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gt(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '>', $value, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gte(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '>=', $value, 'en_US', 'mobile');
    }

    function it_adds_a_less_than_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lt(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '<', $value, 'en_US', 'mobile');
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(
        $attrValidatorHelper,
        $measureManager,
        $measureConverter,
        Builder $queryBuilder,
        AttributeInterface $metric
    ) {
        $attrValidatorHelper->validateLocale($metric, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($metric, Argument::any())->shouldBeCalled();

        $value = ['amount' => 22.5, 'unit' => 'CENTIMETER'];
        $metric->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']);
        $measureConverter->setFamily('length')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('CENTIMETER', 22.5)->willReturn(0.225);

        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lte(0.225)->shouldBeCalled();

        $this->addAttributeFilter($metric, '<=', $value, 'en_US', 'mobile');
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('metric_code');

        $value = ['unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'metric_code',
                'amount',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 459];
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'metric_code',
                'unit',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 'foo', 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'metric_code',
                'key "amount" has to be a numeric, "string" given',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['amount' => 132, 'unit' => 42];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'metric_code',
                'key "unit" has to be a string, "integer" given',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
                $value
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);
    }

    function it_throws_an_exception_if_value_had_not_a_valid_unit($measureManager, AttributeInterface $attribute)
    {
        $attribute->getMetricFamily()->willReturn('length');
        $measureManager->getUnitSymbolsForFamily('length')->willReturn(
            ['CENTIMETER' => 'cm', 'METER' => 'm', 'KILOMETER' => 'km']
        );

        $attribute->getCode()->willReturn('metric_code');
        $value = ['amount' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'metric_code',
                'unit',
                'The unit does not exist in the attribute\'s family "length"',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
                'foo'
            )
        )->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}

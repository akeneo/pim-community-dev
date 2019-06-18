<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\MetricFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class MetricFilterSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attributeValidatorHelper,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter
    ) {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $measureManager,
            $measureConverter,
            ['pim_catalog_metric'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                '<',
                '<=',
                '=',
                '>=',
                '>',
                'EMPTY',
                'NOT EMPTY',
                '!=',
            ]
        );
        $this->supportsOperator('<=')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_metric_attribute(AttributeInterface $metric, AttributeInterface $tags)
    {
        $metric->getType()->willReturn('pim_catalog_metric');
        $tags->getType()->willReturn('pim_catalog_multiselect');

        $this->getAttributeTypes()->shouldReturn(
            [
                'pim_catalog_metric',
            ]
        );

        $this->supportsAttribute($metric)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_lower_than(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'range' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => ['lt' => 1000],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::LOWER_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_lower_than_or_equal(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'range' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => ['lte' => 1000],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'term' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => 1000,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::EQUALS,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_not_equals(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addMustNot(
            [
                'term' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => 1000,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.weight-metric.ecommerce.en_US.base_data',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::NOT_EQUAL,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_than_or_equals(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'range' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => ['gte' => 1000],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'range' => [
                    'values.weight-metric.ecommerce.en_US.base_data' => ['gt' => 1000],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::GREATER_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_empty(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.weight-metric.ecommerce.en_US.base_data',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($metric, Operators::IS_EMPTY, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.weight-metric.ecommerce.en_US.base_data',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($metric, Operators::IS_NOT_EMPTY, [], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $metric)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter',
            [$metric, Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'GRAM'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'weight',
                MetricFilter::class,
                10
            )
        )->during('addAttributeFilter', [$metric, Operators::LOWER_THAN, 10, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_array_value_has_no_amount(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'weight',
                'amount',
                MetricFilter::class,
                ['value' => 10, 'unit_type' => 'kilogram']
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::LOWER_THAN, ['value' => 10, 'unit_type' => 'kilogram'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_the_given_array_value_has_no_unit(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'weight',
                'unit',
                MetricFilter::class,
                ['amount' => 10, 'unit_type' => 'kilogram']
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::LOWER_THAN, ['amount' => 10, 'unit_type' => 'kilogram'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_the_given_amount_is_null(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getCode()->willReturn('weight');

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'weight',
                sprintf('key "amount" has to be a numeric, "%s" given', gettype(null)),
                MetricFilter::class,
                ['amount' => null, 'unit' => 'kilogram']
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::LOWER_THAN, ['amount' => null, 'unit' => 'kilogram'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_the_given_amount_is_not_numeric(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'weight',
                sprintf('key "amount" has to be a numeric, "%s" given', gettype('10')),
                MetricFilter::class,
                ['amount' => 'NOT_NUMERIC', 'unit' => 'kilogram']
            )
        )->during(
            'addAttributeFilter',
            [
                $metric,
                Operators::LOWER_THAN,
                ['amount' => 'NOT_NUMERIC', 'unit' => 'kilogram'],
                'en_US',
                'ecommerce',
                [],
            ]
        );
    }

    function it_throws_an_exception_when_the_given_unit_is_not_a_string(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'weight',
                sprintf('key "unit" has to be a string, "%s" given', gettype(10)),
                MetricFilter::class,
                ['amount' => 10, 'unit' => 10]
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::LOWER_THAN, ['amount' => 10, 'unit' => 10], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_the_given_unit_is_not_known_of_the_metric_family(
        $attributeValidatorHelper,
        $measureManager,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM', 'KILOGRAM']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'weight',
                'unit',
                'The unit does not exist in the attribute\'s family "Weight"',
                MetricFilter::class,
                'UNKNOWN_UNIT'
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::LOWER_THAN, ['amount' => 10, 'unit' => 'UNKNOWN_UNIT'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributeValidatorHelper->validateLocale($metric, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($metric, 'ecommerce')->shouldBeCalled();

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                MetricFilter::class
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::IN_CHILDREN_LIST, ['amount' => 1, 'unit' => 'KILOGRAM'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');
        $metric->isLocaleSpecific()->willReturn(true);
        $metric->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "size" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($metric, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'weight',
                MetricFilter::class,
                $e
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::CONTAINS, ['amount' => 10, 'unit' => 'KILOGRAM'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');
        $metric->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "weight" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($metric, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'weight',
                MetricFilter::class,
                $e
            )
        )->during(
            'addAttributeFilter',
            [$metric, Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'KILOGRAM'], 'en_US', 'ecommerce', []]
        );
    }
}

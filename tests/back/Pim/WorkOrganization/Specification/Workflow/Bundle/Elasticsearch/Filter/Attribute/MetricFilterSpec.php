<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\MetricFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class MetricFilterSpec extends ObjectBehavior
{
    function let(
        ProposalAttributePathResolver $attributePathResolver,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter
    ) {
        $this->beConstructedWith(
            $attributePathResolver,
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
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.weight-metric.ecommerce.en_US.base_data' => ['lt' => 1000]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::LOWER_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM']
        );
    }

    function it_adds_a_filter_with_operator_lower_than_or_equal(
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.weight-metric.ecommerce.en_US.base_data' => ['lte' => 1000]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM']);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.weight-metric.ecommerce.en_US.base_data' => 1000]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::EQUALS,
            ['amount' => 1, 'unit' => 'KILOGRAM']
        );
    }

    function it_adds_a_filter_with_operator_not_equals(
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.weight-metric.ecommerce.en_US.base_data' => 1000]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.weight-metric.ecommerce.en_US.base_data']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::NOT_EQUAL,
            ['amount' => 1, 'unit' => 'KILOGRAM']
        );
    }

    function it_adds_a_filter_with_operator_greater_than_or_equals(
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.weight-metric.ecommerce.en_US.base_data' => ['gte' => 1000]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM']
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $metric->getMetricFamily()->willReturn('Weight');
        $measureManager->getUnitSymbolsForFamily('Weight')->willReturn(['GRAM' => 1000, 'KILOGRAM' => 1]);

        $measureConverter->setFamily('Weight')->shouldBeCalled();
        $measureConverter->convertBaseToStandard('KILOGRAM', 1)->willReturn(1000);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.weight-metric.ecommerce.en_US.base_data' => ['gt' => 1000]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $metric,
            Operators::GREATER_THAN,
            ['amount' => 1, 'unit' => 'KILOGRAM']
        );
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.weight-metric.ecommerce.en_US.base_data']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['weight']]],
                        ['terms' => ['attributes_of_ancestors' => ['weight']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($metric, Operators::IS_EMPTY, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_empty(
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.weight-metric.ecommerce.en_US.base_data']]
                    ],
                    'minimum_should_match' => 1
                ]
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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        $measureManager,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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
        $attributePathResolver,
        $measureManager,
        $measureConverter,
        AttributeInterface $metric,
        SearchQueryBuilder $sqb
    ) {
        $metric->getCode()->willReturn('weight');
        $metric->getBackendType()->willReturn('metric');

        $attributePathResolver->getAttributePaths($metric)->willReturn(['values.weight-metric.ecommerce.en_US']);

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

}

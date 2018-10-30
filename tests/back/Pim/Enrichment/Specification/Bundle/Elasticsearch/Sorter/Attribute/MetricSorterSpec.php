<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute\MetricSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class MetricSorterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith($attributeValidatorHelper, ['pim_catalog_metric']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricSorter::class);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement(AttributeSorterInterface::class);
    }

    function it_adds_a_sorter_with_operator_ascendant_no_locale_and_no_scope(
        $attributeValidatorHelper,
        AttributeInterface $aMetric,
        SearchQueryBuilder $sqb
    ) {
        $aMetric->getCode()->willReturn('a_metric');
        $aMetric->getBackendType()->willReturn('metric');
        $sqb->addSort([
            'values.a_metric-metric.<all_channels>.<all_locales>.base_data' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();


        $attributeValidatorHelper->validateLocale($aMetric, null)->shouldBeCalled();
        $attributeValidatorHelper->validateScope($aMetric, null)->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aMetric, DIRECTIONS::ASCENDING, null, null);
    }

    function it_adds_a_sorter_with_operator_ascendant_locale_and_scope(
        $attributeValidatorHelper,
        AttributeInterface $aMetric,
        SearchQueryBuilder $sqb
    ) {
        $aMetric->getCode()->willReturn('a_metric');
        $aMetric->getBackendType()->willReturn('metric');

        $sqb->addSort([
            'values.a_metric-metric.ecommerce.fr_FR.base_data' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $attributeValidatorHelper->validateLocale($aMetric, 'fr_FR')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($aMetric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aMetric, DIRECTIONS::ASCENDING, 'fr_FR', 'ecommerce');
    }

    function it_adds_a_sorter_with_operator_descendant_locale_and_scope(
        $attributeValidatorHelper,
        AttributeInterface $aMetric,
        SearchQueryBuilder $sqb
    ) {
        $aMetric->getCode()->willReturn('a_metric');
        $aMetric->getBackendType()->willReturn('metric');

        $sqb->addSort([
            'values.a_metric-metric.ecommerce.fr_FR.base_data' => [
                'order' => 'DESC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $attributeValidatorHelper->validateLocale($aMetric, 'fr_FR')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($aMetric, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aMetric, DIRECTIONS::DESCENDING, 'fr_FR', 'ecommerce');
    }

    function it_supports_only_metrics_attribute(
        AttributeInterface $aMetric,
        AttributeInterface $aPrice
    ) {
        $aMetric->getType()->willReturn('pim_catalog_metric');
        $aPrice->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($aMetric)->shouldReturn(true);
        $this->supportsAttribute($aPrice)->shouldReturn(false);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        AttributeInterface $aMetric
    ) {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addAttributeSorter', [$aMetric, Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(
        AttributeInterface $aMetric,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                MetricSorter::class
            )
        )->during('addAttributeSorter', [$aMetric, 'A_BAD_DIRECTION']);
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

        $e = new \LogicException('Attribute "weight" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($metric, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'weight',
                MetricSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$metric, Directions::ASCENDING, 'en_US', 'ecommerce', []]
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
                MetricSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$metric, Directions::DESCENDING, 'en_US', 'ecommerce', []]
        );
    }
}

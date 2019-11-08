<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MetricNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_metric_product_value(
        MetricValueInterface $metricValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_metric_attribute')->willReturn(new Attribute(
            'my_metric_attribute',
            'pim_catalog_metric',
            [],
            false,
            false,
            null,
            true,
            'metric',
            []
        ));
        $getAttributes->forCode('my_text_attribute')->willReturn(new Attribute(
            'my_text_attribute',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($metricValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_an_empty_metric_product_value_with_no_locale_and_no_channel(
        MetricValueInterface $metricValue,
        GetAttributes $getAttributes
    ) {
        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn(null);
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn(null);

        $getAttributes->forCode('my_metric_attribute')->willReturn(new Attribute(
            'my_metric_attribute',
            'pim_catalog_metric',
            [],
            false,
            false,
            null,
            true,
            'metric',
            []
        ));

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_metric_attribute-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_metric_product_value_with_no_locale_and_no_channel(
        ValueInterface $metricValue,
        MetricInterface $metric,
        GetAttributes $getAttributes
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn(null);
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $getAttributes->forCode('my_metric_attribute')->willReturn(new Attribute(
            'my_metric_attribute',
            'pim_catalog_metric',
            [],
            false,
            false,
            null,
            true,
            'metric',
            []
        ));

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_metric_attribute-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'data'      => '125.12',
                        'base_data' => '0.12512',
                        'unit'      => 'GRAM',
                        'base_unit' => 'KILOGRAM',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_metric_product_value_with_locale(
        ValueInterface $metricValue,
        MetricInterface $metric,
        GetAttributes $getAttributes
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn('en_US');
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $getAttributes->forCode('my_metric_attribute')->willReturn(new Attribute(
            'my_metric_attribute',
            'pim_catalog_metric',
            [],
            true,
            false,
            null,
            true,
            'metric',
            []
        ));

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_metric_attribute-metric' => [
                '<all_channels>' => [
                    'en_US' => [
                        'data'      => '125.12',
                        'base_data' => '0.12512',
                        'unit'      => 'GRAM',
                        'base_unit' => 'KILOGRAM',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_integer_product_value_with_locale_and_channel(
        ValueInterface $metricValue,
        MetricInterface $metric,
        GetAttributes $getAttributes
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn('fr_FR');
        $metricValue->getScopeCode()->willReturn('ecommerce');
        $metricValue->getData()->willReturn($metric);

        $getAttributes->forCode('my_metric_attribute')->willReturn(new Attribute(
            'my_metric_attribute',
            'pim_catalog_metric',
            [],
            true,
            true,
            null,
            false,
            'metric',
            []
        ));

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_metric_attribute-metric' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'data'      => '125.12',
                        'base_data' => '0.12512',
                        'unit'      => 'GRAM',
                        'base_unit' => 'KILOGRAM',
                    ],
                ],
            ],
        ]);
    }
}

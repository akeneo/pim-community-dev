<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModelFormat\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductFormat\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\MetricNormalizer;
use Pim\Component\Catalog\Value\MetricValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
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
        AttributeInterface $metricAttribute,
        AttributeInterface $textAttribute
    ) {
        $metricValue->getAttribute()->willReturn($metricAttribute);

        $textValue->getAttribute()->willReturn($textAttribute);
        $textAttribute->getBackendType()->willReturn('text');

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($metricValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($metricValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($metricValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_an_empty_metric_product_value_with_no_locale_and_no_channel(
        MetricValueInterface $metricValue,
        AttributeInterface $metricAttribute
    ) {
        $metricValue->getAttribute()->willReturn($metricAttribute);
        $metricValue->getLocale()->willReturn(null);
        $metricValue->getScope()->willReturn(null);
        $metricValue->getData()->willReturn(null);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');

        $this->normalize($metricValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'weight-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_metric_product_value_with_no_locale_and_no_channel(
        ValueInterface $metricValue,
        AttributeInterface $metricAttribute,
        MetricInterface $metric
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttribute()->willReturn($metricAttribute);
        $metricValue->getLocale()->willReturn(null);
        $metricValue->getScope()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');

        $this->normalize($metricValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'weight-metric' => [
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
        AttributeInterface $metricAttribute,
        MetricInterface $metric
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttribute()->willReturn($metricAttribute);
        $metricValue->getLocale()->willReturn('en_US');
        $metricValue->getScope()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');

        $this->normalize($metricValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'weight-metric' => [
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
        AttributeInterface $metricAttribute,
        MetricInterface $metric
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttribute()->willReturn($metricAttribute);
        $metricValue->getLocale()->willReturn('fr_FR');
        $metricValue->getScope()->willReturn('ecommerce');
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->isDecimalsAllowed()->willReturn(false);
        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');

        $this->normalize($metricValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'weight-metric' => [
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

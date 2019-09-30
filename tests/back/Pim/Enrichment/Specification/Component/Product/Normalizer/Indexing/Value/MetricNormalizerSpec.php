<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MetricNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $metricAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $attributeRepository->findOneByidentifier('my_metric_attribute')->willReturn($metricAttribute);
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $attributeRepository->findOneByidentifier('my_text_attribute')->willReturn($textAttribute);

        $textAttribute->getBackendType()->willReturn('text');

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
        AttributeInterface $metricAttribute,
        $attributeRepository
    ) {
        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn(null);
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn(null);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');
        $attributeRepository->findOneByIdentifier('my_metric_attribute')->willReturn($metricAttribute);

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        MetricInterface $metric,
        $attributeRepository
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn(null);
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');
        $attributeRepository->findOneByIdentifier('my_metric_attribute')->willReturn($metricAttribute);

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        MetricInterface $metric,
        $attributeRepository
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn('en_US');
        $metricValue->getScopeCode()->willReturn(null);
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');
        $attributeRepository->findOneByIdentifier('my_metric_attribute')->willReturn($metricAttribute);

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        MetricInterface $metric,
        $attributeRepository
    ) {
        $metric->getData()->willReturn(125.12);
        $metric->getBaseData()->willReturn(0.12512);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');

        $metricValue->getAttributeCode()->willReturn('my_metric_attribute');
        $metricValue->getLocaleCode()->willReturn('fr_FR');
        $metricValue->getScopeCode()->willReturn('ecommerce');
        $metricValue->getData()->willReturn($metric);

        $metricAttribute->isDecimalsAllowed()->willReturn(false);
        $metricAttribute->getCode()->willReturn('weight');
        $metricAttribute->getBackendType()->willReturn('metric');
        $attributeRepository->findOneByIdentifier('my_metric_attribute')->willReturn($metricAttribute);

        $this->normalize($metricValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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

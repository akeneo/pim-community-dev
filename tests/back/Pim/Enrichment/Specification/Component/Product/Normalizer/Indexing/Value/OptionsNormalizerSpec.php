<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\OptionsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_options_product_value(
        OptionsValueInterface $optionsValue,
        ValueInterface $textValue,
        AttributeInterface $optionsAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('my_options_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $attributeRepository->findOneByIdentifier('my_options_attribute')->willReturn($optionsAttribute);
        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);

        $optionsAttribute->getBackendType()->willReturn('options');
        $textAttribute->getBackendType()->willReturn('text');

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($optionsValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalize_an_empty_options_product_value(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('tags');
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');
        $attributeRepository->findOneByIdentifier('tags')->willReturn($optionsAttribute);

        $optionsValue->getOptionCodes()->willReturn([]);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalize_an_options_product_value_with_no_locale_and_no_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('tags');
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');
        $attributeRepository->findOneByIdentifier('tags')->willReturn($optionsAttribute);

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('tags');
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocaleCode()->willReturn('en_US');
        $optionsValue->getScopeCode()->willReturn(null);

        $optionsAttribute->getCode()->willReturn('tags');
        $attributeRepository->findOneByIdentifier('tags')->willReturn($optionsAttribute);

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'tags-options' => [
                    '<all_channels>' => [
                        'en_US' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('tags');
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocaleCode()->willReturn(null);
        $optionsValue->getScopeCode()->willReturn('ecommerce');

        $optionsAttribute->getCode()->willReturn('tags');
        $attributeRepository->findOneByIdentifier('tags')->willReturn($optionsAttribute);

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'tags-options' => [
                    'ecommerce' => [
                        '<all_locales>' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_option_product_value_with_locale_and_channel(
        OptionsValue $optionsValue,
        AttributeInterface $optionsAttribute,
        $attributeRepository
    ) {
        $optionsValue->getAttributeCode()->willReturn('tags');
        $optionsAttribute->getBackendType()->willReturn('options');

        $optionsValue->getLocaleCode()->willReturn('en_US');
        $optionsValue->getScopeCode()->willReturn('ecommerce');

        $optionsAttribute->getCode()->willReturn('tags');
        $attributeRepository->findOneByIdentifier('tags')->willReturn($optionsAttribute);

        $optionsValue->getOptionCodes()->willReturn(['tagA', 'tagB']);

        $this->normalize($optionsValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn(
            [
                'tags-options' => [
                    'ecommerce' => [
                        'en_US' => [
                            'tagA',
                            'tagB',
                        ],
                    ],
                ],
            ]
        );
    }
}

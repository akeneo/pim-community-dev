<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeNormalizer;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(
        NormalizerInterface         $normalizer,
        FieldProviderInterface      $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface     $filterProvider,
        LocalizerInterface          $numberLocalizer
    ): void
    {
        $this->beConstructedWith(
            $normalizer,
            $fieldProvider,
            $emptyValueProvider,
            $filterProvider,
            $numberLocalizer
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    public function it_normalizes_an_attribute(
        NormalizerInterface         $normalizer,
        FieldProviderInterface      $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface     $filterProvider,
        AttributeInterface          $priceAttribute
    ): void
    {
        $standardNormalizedData = [
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
            'group' => 'marketing',
            'unique' => false,
            'useable_as_grid_filter' => true,
            'allowed_extensions' => null,
            'metric_family' => null,
            'default_metric_unit' => null,
            'reference_data_name' => null,
            'available_locales' => [],
            'max_characters' => null,
            'validation_rule' => null,
            'validation_regexp' => null,
            'wysiwyg_enabled' => null,
            'number_min' => null,
            'number_max' => null,
            'decimals_allowed' => true,
            'negative_allowed' => null,
            'date_min' => null,
            'date_max' => null,
            'max_file_size' => null,
            'minimum_input_length' => null,
            'sort_order' => 0,
            'localizable' => false,
            'scopable' => false,
            'labels' => [],
            'auto_option_sorting' => null,
            'guidelines' => ['en_US' => 'the guidelines'],
        ];
        $normalizer->normalize($priceAttribute, 'standard', Argument::any())->willReturn($standardNormalizedData);

        $priceAttribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $priceAttribute->getDateMin()->willReturn(null);
        $priceAttribute->getDateMax()->willReturn(null);
        $emptyValueProvider->getEmptyValue($priceAttribute)->willReturn([]);
        $fieldProvider->getField($priceAttribute)->willReturn('akeneo-text-field');
        $filterProvider->getFilters($priceAttribute)->willReturn(['product-export-builder' => 'akeneo-attribute-string-filter']);
        $priceAttribute->isLocaleSpecific()->willReturn(false);
        $priceAttribute->getId()->willReturn(12);

        $expectedNormalizedData = array_merge($standardNormalizedData, [
            'empty_value' => [],
            'field_type' => 'akeneo-text-field',
            'filter_types' => ['product-export-builder' => 'akeneo-attribute-string-filter'],
            'is_locale_specific' => false,
            'meta' => ['id' => 12],
        ]);

        $this->normalize($priceAttribute, 'internal_api', [])->shouldReturn($expectedNormalizedData);
    }

    public function it_normalizes_an_attribute_with_localized_data(
        NormalizerInterface         $normalizer,
        FieldProviderInterface      $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface     $filterProvider,
        LocalizerInterface          $numberLocalizer,
        AttributeInterface          $priceAttribute
    ): void
    {
        $standardNormalizedData = [
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
            'group' => 'marketing',
            'unique' => false,
            'useable_as_grid_filter' => true,
            'allowed_extensions' => null,
            'metric_family' => null,
            'default_metric_unit' => null,
            'reference_data_name' => null,
            'available_locales' => [],
            'max_characters' => null,
            'validation_rule' => null,
            'validation_regexp' => null,
            'wysiwyg_enabled' => null,
            'number_min' => '20.5',
            'number_max' => '4000.8',
            'decimals_allowed' => true,
            'negative_allowed' => null,
            'date_min' => null,
            'date_max' => null,
            'max_file_size' => null,
            'minimum_input_length' => null,
            'sort_order' => 0,
            'localizable' => false,
            'scopable' => false,
            'labels' => [],
            'auto_option_sorting' => null,
            'guidelines' => ['en_US' => 'the guidelines'],
        ];

        $normalizer->normalize($priceAttribute, 'standard', Argument::any())->willReturn($standardNormalizedData);

        $priceAttribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $priceAttribute->getDateMin()->willReturn(null);
        $priceAttribute->getDateMax()->willReturn(null);
        $emptyValueProvider->getEmptyValue($priceAttribute)->willReturn([]);
        $fieldProvider->getField($priceAttribute)->willReturn('akeneo-text-field');
        $filterProvider->getFilters($priceAttribute)->willReturn(['product-export-builder' => 'akeneo-attribute-string-filter']);
        $priceAttribute->isLocaleSpecific()->willReturn(false);
        $numberLocalizer->localize('20.5', ['locale' => 'fr_FR'])->willReturn('20,5');
        $numberLocalizer->localize('4000.8', ['locale' => 'fr_FR'])->willReturn('4000,8');
        $priceAttribute->getId()->willReturn(12);

        $expectedNormalizedData = array_merge($standardNormalizedData, [
            'empty_value' => [],
            'field_type' => 'akeneo-text-field',
            'filter_types' => ['product-export-builder' => 'akeneo-attribute-string-filter'],
            'is_locale_specific' => false,
            'meta' => ['id' => 12],
            'number_min' => '20,5',
            'number_max' => '4000,8',
        ]);

        $this->normalize($priceAttribute, 'internal_api', ['locale' => 'fr_FR'])->shouldReturn($expectedNormalizedData);
    }

    public function it_supports_attributes_and_internal_api(AttributeInterface $price): void
    {
        $this->supportsNormalization($price, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }

    public function it_normalizes_identifier_attribute(
        NormalizerInterface         $normalizer,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $attribute->isMainIdentifier()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $attribute->getId()->willReturn(123);

        $standardNormalizedData = [
            'code' => 'my_identifier_attribute',
            'labels' => ['en_US' => 'english_label'],
        ];
        $normalizer->normalize($attribute, 'standard', Argument::any())->willReturn($standardNormalizedData);

        $expectedNormalizedData = array_merge($standardNormalizedData, [
            'empty_value' => null,
            'field_type' => null,
            'filter_types' => null,
            'is_locale_specific' => false,
            'date_min' => null,
            'date_max' => null,
            'meta' => ['id' => 123],
            'is_main_identifier' => true,
        ]);
        $this->normalize($attribute, 'internal_api', [])->shouldReturn($expectedNormalizedData);
    }
}

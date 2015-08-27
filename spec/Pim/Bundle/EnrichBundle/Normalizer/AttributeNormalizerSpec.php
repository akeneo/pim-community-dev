<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer, FieldProviderInterface $fieldProvider, EmptyValueProviderInterface $emptyValueProvider)
    {
        $this->beConstructedWith($normalizer, $fieldProvider, $emptyValueProvider);
    }

    public function it_adds_the_attribute_id_to_the_normalized_attribute($normalizer, $fieldProvider, $emptyValueProvider, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'price']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('pim_catalog_text');
        $price->isLocaleSpecific()->willReturn(false);
        $price->getLocaleSpecificCodes()->willReturn([]);

        $price->getDateMin()->willReturn(null);
        $price->getDateMax()->willReturn(null);
        $price->getMaxCharacters()->willReturn('');
        $price->getValidationRule()->willReturn('');
        $price->getValidationRegexp()->willReturn('');
        $price->getNumberMin()->willReturn('');
        $price->getNumberMax()->willReturn('');
        $price->isDecimalsAllowed()->willReturn(true);
        $price->isNegativeAllowed()->willReturn(false);
        $price->getMetricFamily()->willReturn('');
        $price->getDefaultMetricUnit()->willReturn('');
        $price->getMaxFileSize()->willReturn('');

        $fieldProvider->getField($price)->willReturn('akeneo-text-field');
        $emptyValueProvider->getEmptyValue($price)->willReturn([]);

        $this->normalize($price, 'internal_api', [])->shouldReturn(
            [
                'code'                  => 'price',
                'id'                    => 12,
                'wysiwyg_enabled'       => false,
                'empty_value'           => [],
                'field_type'            => 'akeneo-text-field',
                'is_locale_specific'    => 0,
                'locale_specific_codes' => [],
                'max_characters'        => '',
                'validation_rule'       => '',
                'validation_regexp'     => '',
                'number_min'            => '',
                'number_max'            => '',
                'decimals_allowed'      => true,
                'negative_allowed'      => false,
                'date_min'              => '',
                'date_max'              => '',
                'metric_family'         => '',
                'default_metric_unit'   => '',
                'max_file_size'         => ''
            ]
        );
    }

    public function it_adds_the_attribute_empty_value_to_the_normalized_attribute($normalizer, $fieldProvider, $emptyValueProvider, AttributeInterface $attribute)
    {
        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'text']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(true);
        $attribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getLocaleSpecificCodes()->willReturn([]);

        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $attribute->getMaxCharacters()->willReturn('2048');
        $attribute->getValidationRule()->willReturn('');
        $attribute->getValidationRegexp()->willReturn('');
        $attribute->getNumberMin()->willReturn('');
        $attribute->getNumberMax()->willReturn('');
        $attribute->isDecimalsAllowed()->willReturn('');
        $attribute->isNegativeAllowed()->willReturn('');
        $attribute->getMetricFamily()->willReturn('');
        $attribute->getDefaultMetricUnit()->willReturn('');
        $attribute->getMaxFileSize()->willReturn('');

        $fieldProvider->getField($attribute)->willReturn('akeneo-text-field');
        $emptyValueProvider->getEmptyValue($attribute)->willReturn([]);

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'text',
            'id'                    => 12,
            'wysiwyg_enabled'       => true,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => '',
            'max_file_size'         => ''
        ]);

        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'boolean']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(true);
        $attribute->getAttributeType()->willReturn('pim_catalog_boolean');

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'boolean',
            'id'                    => 12,
            'wysiwyg_enabled'       => true,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => '',
            'max_file_size'         => ''
        ]);

        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'collection']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getAttributeType()->willReturn('pim_catalog_attribute_collection');

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'collection',
            'id'                    => 12,
            'wysiwyg_enabled'       => false,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => '',
            'max_file_size'         => ''
        ]);

        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'collection']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'collection',
            'id'                    => 12,
            'wysiwyg_enabled'       => false,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => '',
            'max_file_size'         => ''
        ]);

        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'metric']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getDefaultMetricUnit()->willReturn('kg');

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'metric',
            'id'                    => 12,
            'wysiwyg_enabled'       => false,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => 'kg',
            'max_file_size'         => ''
        ]);

        $normalizer->normalize($attribute, 'json', [])->willReturn(['code' => 'default']);
        $attribute->getId()->willReturn(12);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getAttributeType()->willReturn('unknown');

        $this->normalize($attribute, 'internal_api', [])->shouldReturn([
            'code'                  => 'default',
            'id'                    => 12,
            'wysiwyg_enabled'       => false,
            'empty_value'           => [],
            'field_type'            => 'akeneo-text-field',
            'is_locale_specific'    => 0,
            'locale_specific_codes' => [],
            'max_characters'        => '2048',
            'validation_rule'       => '',
            'validation_regexp'     => '',
            'number_min'            => '',
            'number_max'            => '',
            'decimals_allowed'      => '',
            'negative_allowed'      => '',
            'date_min'              => '',
            'date_max'              => '',
            'metric_family'         => '',
            'default_metric_unit'   => 'kg',
            'max_file_size'         => ''
        ]);
    }

    public function it_supports_attributes_and_internal_api(AttributeInterface $price)
    {
        $this->supportsNormalization($price, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}

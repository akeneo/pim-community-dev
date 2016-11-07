<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transnormalizer,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $this->beConstructedWith($transnormalizer);
        $transnormalizer->normalize(Argument::cetera())->willReturn([]);
        $attribute->getAttributeType()->willReturn('Yes/No');
        $attribute->getCode()->willReturn('attribute_size');
        $attribute->getGroup()->willReturn($attributeGroup);
        $attributeGroup->getCode()->willReturn('size');
        $attribute->isUnique()->willReturn(true);
        $attribute->isUseableAsGridFilter()->willReturn(false);
        $attribute->getAllowedExtensions()->willReturn(['csv', 'xml', 'json']);
        $attribute->getMetricFamily()->willReturn('Length');
        $attribute->getDefaultMetricUnit()->willReturn('Centimenter');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getLocaleSpecificCodes()->willReturn(['en_US', 'fr_FR']);
        $attribute->getMaxCharacters()->willReturn(null);
        $attribute->getValidationRule()->willReturn(null);
        $attribute->getValidationRegexp()->willReturn(null);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getNumberMin()->willReturn('');
        $attribute->getNumberMax()->willReturn('');
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->isNegativeAllowed()->willReturn(false);
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $attribute->getMaxFileSize()->willReturn(null);
        $attribute->getMinimumInputLength()->willReturn(null);
        $attribute->getSortOrder()->willReturn(0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Structured\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_normalization_into_json_and_xml($attribute)
    {
        $this->supportsNormalization($attribute, 'csv')->shouldBe(false);
        $this->supportsNormalization($attribute, 'json')->shouldBe(true);
        $this->supportsNormalization($attribute, 'xml')->shouldBe(true);
    }

    function it_normalizes_attribute($attribute)
    {
        $this->normalize($attribute)->shouldReturn(
            [
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'max_characters'         => '',
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => false,
                'number_min'             => '',
                'number_max'             => '',
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => '',
                'date_max'               => '',
                'max_file_size'          => '',
                'minimum_input_length'   => '',
                'sort_order'             => 0,
                'localizable'            => true,
                'scopable'               => false,
            ]
        );
    }

    function it_normalizes_attribute_for_versioning(
        $attribute,
        AttributeOptionInterface $size,
        AttributeOptionValueInterface $en,
        AttributeOptionValueInterface $fr
    ) {
        $attribute->getLocaleSpecificCodes()->willReturn(['en_US', 'fr_FR']);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getOptions()->willReturn([$size]);
        $size->getCode()->willReturn('size');
        $size->getOptionValues()->willReturn([$en, $fr]);
        $en->getLocale()->willReturn('en_US');
        $en->getValue()->willReturn('big');
        $fr->getLocale()->willReturn('fr_FR');
        $fr->getValue()->willReturn('grand');
        $attribute->getSortOrder()->willReturn(1);
        $attribute->isRequired()->willReturn(false);
        $attribute->getMaxCharacters()->willReturn(null);
        $attribute->getValidationRule()->willReturn(null);
        $attribute->getValidationRegexp()->willReturn(null);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getNumberMin()->willReturn(1);
        $attribute->getNumberMax()->willReturn(10);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->isNegativeAllowed()->willReturn(false);
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $attribute->getMaxFileSize()->willReturn(0);
        $attribute->getSortOrder()->willReturn(0);
        $this->normalize($attribute, null, ['versioning' => true])->shouldReturn(
            [
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'max_characters'         => '',
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => false,
                'number_min'             => '1',
                'number_max'             => '10',
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => '',
                'date_max'               => '',
                'max_file_size'          => '0',
                'minimum_input_length'   => '',
                'sort_order'             => 0,
                'localizable'            => true,
                'scope'                  => 'Channel',
                'options'                => [
                    'size' => [
                        'en_US' => 'big',
                        'fr_FR' => 'grand'
                    ]
                ],
                'required'               => false,
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
            ]
        );
    }
}

<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
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
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Structured\AttributeNormalizer');
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
                'unique'                 => 1,
                'useable_as_grid_filter' => 0,
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'localizable'            => 1,
                'scopable'               => 0
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
        $this->normalize($attribute, null, ['versioning' => true])->shouldReturn(
            [
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => 1,
                'useable_as_grid_filter' => 0,
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'localizable'            => true,
                'scope'                  => 'Channel',
                'options'                => [
                    'size' => [
                        'en_US' => 'big',
                        'fr_FR' => 'grand'
                    ]
                ],
                'sort_order'             => 1,
                'required'               => 0,
                'max_characters'         => '',
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => '',
                'number_min'             => '1',
                'number_max'             => '10',
                'decimals_allowed'       => '',
                'negative_allowed'       => '',
                'date_min'               => '',
                'date_max'               => '',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'max_file_size'          => '0'
            ]
        );
    }
}

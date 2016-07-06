<?php

namespace spec\Pim\Component\Connector\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\Normalizer\Flat\TranslationNormalizer;
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
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\Flat\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_normalization_into_csv($attribute)
    {
        $this->supportsNormalization($attribute, 'csv')->shouldBe(true);
        $this->supportsNormalization($attribute, 'json')->shouldBe(false);
        $this->supportsNormalization($attribute, 'xml')->shouldBe(false);
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
                'available_locales'      => 'en_US,fr_FR',
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
                'scopable'               => false
            ]
        );
    }

    function it_normalizes_attribute_for_versioning($attribute)
    {
        $attribute->getLocaleSpecificCodes()->willReturn(['en_US', 'fr_FR']);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $size = new AttributeOption();
        $size->setCode('size');
        $en = new AttributeOptionValue();
        $fr =new AttributeOptionValue();
        $en->setLocale('en_US');
        $en->setValue('big');
        $fr->setLocale('fr_FR');
        $fr->setValue('grand');
        $size->addOptionValue($en);
        $size->addOptionValue($fr);
        $attribute->getOptions()->willReturn(new ArrayCollection([$size]));
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
        $attribute->getSortOrder()->willReturn(1);
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
                'available_locales'      => 'en_US,fr_FR',
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
                'sort_order'             => 1,
                'localizable'            => true,
                'scope'                  => 'Channel',
                'options'                => 'Code:size,en_US:big,fr_FR:grand',
                'required'               => false,
            ]
        );
    }
}

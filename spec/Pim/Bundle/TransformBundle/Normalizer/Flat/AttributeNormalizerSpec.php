<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
        $attribute->getSortOrder()->willReturn(10);
        $attribute->getLocaleSpecificCodes()->willReturn(['en_US', 'fr_FR']);
        $attribute->isUseableAsGridFilter()->willReturn(false);
        $attribute->getMaxCharacters()->willReturn(255);
        $attribute->getAllowedExtensions()->willReturn(['csv', 'xml', 'json']);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->isNegativeAllowed()->willReturn(false);
        $attribute->getMetricFamily()->willReturn('Length');
        $attribute->getDefaultMetricUnit()->willReturn('Centimenter');
        $attribute->getMaxFileSize()->willReturn(1000);
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\AttributeNormalizer');
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
                'unique'                 => 1,
                'sort_order'             => 10,
                'available_locales'      => 'en_US,fr_FR',
                'useable_as_grid_filter' => 0,
                'max_characters'         => 255,
                'decimals_allowed'       => 0,
                'negative_allowed'       => 0,
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'max_file_size'          => 1000,
                'localizable'            => 1,
                'scopable'               => 0
            ]
        );
    }

    function it_normalizes_attribute_for_versioning($attribute)
    {
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
        $attribute->isRequired()->willReturn(false);
        $attribute->getValidationRule()->willReturn(null);
        $attribute->getValidationRegexp()->willReturn(null);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getNumberMin()->willReturn(1);
        $attribute->getNumberMax()->willReturn(10);
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $this->normalize($attribute, null, ['versioning' => true])->shouldReturn(
            [
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => 1,
                'sort_order'             => 10,
                'available_locales'      => 'en_US,fr_FR',
                'useable_as_grid_filter' => 0,
                'max_characters'         => '255',
                'decimals_allowed'       => '',
                'negative_allowed'       => '',
                'allowed_extensions'     => 'csv,xml,json',
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'max_file_size'          => '1000',
                'localizable'            => true,
                'scope'                  => 'Channel',
                'options'                => 'Code:size,en_US:big,fr_FR:grand',
                'required'               => 0,
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => '',
                'number_min'             => '1',
                'number_max'             => '10',
                'date_min'               => '',
                'date_max'               => ''
            ]
        );
    }
}

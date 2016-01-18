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
                'available_locales'      => 'en_US,fr_FR',
                'localizable'            => true,
                'scope'                  => 'Channel',
                'options'                => 'Code:size,en_US:big,fr_FR:grand',
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

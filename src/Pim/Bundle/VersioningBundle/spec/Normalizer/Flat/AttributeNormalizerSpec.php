<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Normalizer\Standard\AttributeNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($attributeNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_normalization_into_flat(AttributeInterface $attribute)
    {
        $this->supportsNormalization($attribute, 'flat')->shouldBe(true);
        $this->supportsNormalization($attribute, 'csv')->shouldBe(false);
        $this->supportsNormalization($attribute, 'json')->shouldBe(false);
        $this->supportsNormalization($attribute, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getOptions()->willReturn(new ArrayCollection());

        $translationNormalizer->supportsNormalization(Argument::cetera(), 'flat')
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())
            ->willReturn([
                'label-en_US' => 'My attribute',
                'label-fr_FR' => 'Mon attribut',
            ]);

        $attributeNormalizerStandard->supportsNormalization($attribute, 'standard')
            ->willReturn(true);
        $attributeNormalizerStandard->normalize($attribute, 'standard', [])
            ->willReturn([
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => ['csv', 'xml', 'json'],
                'labels' => [
                    'en_US' => 'My attribute',
                    'fr_FR' => 'Mon attribut'
                ],
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => false,
                'number_min'             => null,
                'number_max'             => null,
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => true,
                'scopable'               => false,
            ]);

        $this->normalize($attribute, 'flat')->shouldReturn(
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
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => false,
                'number_min'             => null,
                'number_max'             => null,
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => true,
                'scopable'               => false,
                'label-en_US'            => 'My attribute',
                'label-fr_FR'            => 'Mon attribut',
                'options'                => null,
            ]
        );
    }

    function it_normalizes_attribute_for_versioning(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeInterface $attribute
    ) {
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

        $translationNormalizer->supportsNormalization(Argument::cetera(), 'flat')
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera(), 'flat')
            ->willReturn([]);

        $attributeNormalizerStandard->supportsNormalization($attribute, 'standard', [])
            ->willReturn(true);
        $attributeNormalizerStandard->normalize($attribute, 'standard', [])
            ->willReturn([
                'type'                   => 'Yes/No',
                'code'                   => 'attribute_size',
                'group'                  => 'size',
                'unique'                 => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => ['csv', 'xml', 'json'],
                'labels' => [],
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => false,
                'number_min'             => '1',
                'number_max'             => '10',
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => '0',
                'minimum_input_length'   => null,
                'sort_order'             => 1,
                'localizable'            => true,
                'scope'                 => 'Channel',
                'required'               => false
            ]);

        $this->normalize($attribute, 'flat', [])->shouldReturn(
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
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => false,
                'number_min'             => '1',
                'number_max'             => '10',
                'decimals_allowed'       => false,
                'negative_allowed'       => false,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => '0',
                'minimum_input_length'   => null,
                'sort_order'             => 1,
                'localizable'            => true,
                'scope'                  => 'Channel',
                'required'               => false,
                'options'                => 'Code:size,en_US:big,fr_FR:grand',
            ]
        );
    }
}

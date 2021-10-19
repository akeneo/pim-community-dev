<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeNormalizer;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAttributeOptionCodes;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        GetAttributeOptionCodes $getAttributeOptionCodes
    ) {
        $this->beConstructedWith($attributeNormalizerStandard, $translationNormalizer, $getAttributeOptionCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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
        AttributeInterface $attribute,
        GetAttributeOptionCodes $getAttributeOptionCodes
    ) {
        $attribute->getCode()->willReturn('attribute_size');
        $getAttributeOptionCodes->forAttributeCode('attribute_size')
            ->willReturn(new \ArrayIterator(['size']));
        $attribute->isRequired()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $translationNormalizer->supportsNormalization(Argument::cetera())
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())
            ->willReturn([]);

        $attributeNormalizerStandard->supportsNormalization($attribute, 'standard', [])
            ->willReturn(true);
        $attributeNormalizerStandard->normalize($attribute, 'standard', [])
            ->willReturn([
                'type' => 'Yes/No',
                'code' => 'attribute_size',
                'group' => 'size',
                'unique' => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions' => ['csv', 'xml', 'json'],
                'labels' => [],
                'metric_family' => 'Length',
                'default_metric_unit' => 'Centimenter',
                'reference_data_name' => 'color',
                'available_locales' => ['en_US', 'fr_FR'],
                'locale_specific' => false,
                'max_characters' => null,
                'validation_rule' => null,
                'validation_regexp' => null,
                'wysiwyg_enabled' => false,
                'number_min' => '1',
                'number_max' => '10',
                'decimals_allowed' => false,
                'negative_allowed' => false,
                'date_min' => null,
                'date_max' => null,
                'max_file_size' => '0',
                'minimum_input_length' => null,
                'sort_order' => 1,
                'localizable' => true,
                'scopable' => true,
                'required' => false,
                'guidelines' => ['en_US' => 'Guidelines in english', 'fr_FR' => 'Guidelines in french'],
            ]);

        $this->normalize($attribute, 'flat', [])->shouldReturn(
            [
                'type' => 'Yes/No',
                'code' => 'attribute_size',
                'group' => 'size',
                'unique' => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions' => 'csv,xml,json',
                'metric_family' => 'Length',
                'default_metric_unit' => 'Centimenter',
                'reference_data_name' => 'color',
                'available_locales' => 'en_US,fr_FR',
                'locale_specific' => false,
                'max_characters' => null,
                'validation_rule' => null,
                'validation_regexp' => null,
                'wysiwyg_enabled' => false,
                'number_min' => '1',
                'number_max' => '10',
                'decimals_allowed' => false,
                'negative_allowed' => false,
                'date_min' => null,
                'date_max' => null,
                'max_file_size' => '0',
                'minimum_input_length' => null,
                'sort_order' => 1,
                'localizable' => true,
                'required' => false,
                'guidelines-en_US' => 'Guidelines in english',
                'guidelines-fr_FR' => 'Guidelines in french',
                'options' => 'Code:size',
                'scope' => 'Channel',
            ]
        );
    }

    function it_normalizes_attribute_with_multiple_options_values(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeInterface $attribute,
        GetAttributeOptionCodes $getAttributeOptionCodes
    ) {
        $attribute->getCode()->willReturn('attribute_size');
        $getAttributeOptionCodes->forAttributeCode('attribute_size')
            ->willReturn(new \ArrayIterator(['size', 'color', 'test']));
        $attribute->isRequired()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $translationNormalizer->supportsNormalization(Argument::cetera())
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())
            ->willReturn([]);

        $attributeNormalizerStandard->normalize($attribute, 'standard', [])
            ->willReturn([
                'type' => 'Yes/No',
                'code' => 'attribute_size',
                'group' => 'size',
                'unique' => true,
                'useable_as_grid_filter' => false,
                'allowed_extensions' => ['csv', 'xml', 'json'],
                'labels' => [],
                'metric_family' => 'Length',
                'default_metric_unit' => 'Centimenter',
                'reference_data_name' => 'color',
                'available_locales' => ['en_US', 'fr_FR'],
                'locale_specific' => false,
                'max_characters' => null,
                'validation_rule' => null,
                'validation_regexp' => null,
                'wysiwyg_enabled' => false,
                'number_min' => '1',
                'number_max' => '10',
                'decimals_allowed' => false,
                'negative_allowed' => false,
                'date_min' => null,
                'date_max' => null,
                'max_file_size' => '0',
                'minimum_input_length' => null,
                'sort_order' => 1,
                'localizable' => true,
                'scopable' => true,
                'required' => false
            ]);
        $attributeNormalizerStandard->supportsNormalization($attribute, 'standard', [])
            ->willReturn(true);
        $this->normalize($attribute, 'flat', [])->shouldHaveKeyWithValue('options', 'Code:size|Code:color|Code:test');
    }

    function it_doesnt_normalize_more_options_than_the_limit(
        AttributeNormalizer $attributeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AttributeInterface $attribute,
        GetAttributeOptionCodes $getAttributeOptionCodes
    ) {
        $attribute->getCode()->willReturn('attribute_size');
        $getAttributeOptionCodes->forAttributeCode('attribute_size')
            ->willReturn(new \ArrayIterator(array_fill(0, 10000 + 1, 'banana')));
        $attribute->isRequired()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $translationNormalizer->supportsNormalization(Argument::cetera())
            ->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())
            ->willReturn([]);

        $attributeNormalizerStandard->normalize($attribute, 'standard', [])
            ->willReturn([
                'allowed_extensions' => ['csv', 'xml', 'json'],
                'labels' => [],
                'metric_family' => 'Length',
                'default_metric_unit' => 'Centimenter',
                'reference_data_name' => 'color',
                'available_locales' => ['en_US', 'fr_FR'],
                'locale_specific' => false,
                'max_characters' => null,
                'validation_rule' => null,
                'validation_regexp' => null,
                'wysiwyg_enabled' => false,
                'number_min' => '1',
                'number_max' => '10',
                'decimals_allowed' => false,
                'negative_allowed' => false,
                'date_min' => null,
                'date_max' => null,
                'max_file_size' => '0',
                'minimum_input_length' => null,
                'sort_order' => 1,
                'localizable' => true,
                'scopable' => true,
                'required' => false
            ]);
        $attributeNormalizerStandard->supportsNormalization($attribute, 'standard', [])
            ->willReturn(true);
        $results = $this->normalize($attribute, 'flat', []);
        $results->shouldHaveKey('options');
        Assert::assertEquals(10000, count(explode('|', $results->getWrappedObject()['options'])));
    }
}

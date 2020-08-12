<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $transNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->beConstructedWith($transNormalizer, $dateTimeNormalizer, ['auto_option_sorting']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AttributeInterface $attribute)
    {
        $this->supportsNormalization($attribute, 'standard')->shouldBe(true);
        $this->supportsNormalization($attribute, 'json')->shouldBe(false);
        $this->supportsNormalization($attribute, 'xml')->shouldBe(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
    }

    function it_normalizes_an_empty_attribute(
        $transNormalizer,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $transNormalizer->normalize(Argument::cetera())->willReturn([]);

        $attribute->getType()->willReturn('Yes/No');
        $attribute->getCode()->willReturn('attribute_size');
        $attribute->getGroup()->willReturn($attributeGroup);
        $attributeGroup->getCode()->willReturn('size');
        $attribute->isUnique()->willReturn(false);
        $attribute->isUseableAsGridFilter()->willReturn(false);
        $attribute->getAllowedExtensions()->willReturn([]);
        $attribute->getMetricFamily()->willReturn('');
        $attribute->getDefaultMetricUnit()->willReturn('');
        $attribute->getReferenceDataName()->willReturn(null);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getAvailableLocaleCodes()->willReturn([]);
        $attribute->getMaxCharacters()->willReturn(null);
        $attribute->getValidationRule()->willReturn(null);
        $attribute->getValidationRegexp()->willReturn(null);
        $attribute->isWysiwygEnabled()->willReturn(false);
        $attribute->getNumberMin()->willReturn(null);
        $attribute->getNumberMax()->willReturn(null);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->isNegativeAllowed()->willReturn(false);
        $attribute->getDateMin()->willReturn(null);
        $attribute->getDateMax()->willReturn(null);
        $attribute->getMaxFileSize()->willReturn(null);
        $attribute->getMinimumInputLength()->willReturn(null);
        $attribute->getSortOrder()->willReturn(0);
        $attribute->getProperty('auto_option_sorting')->willReturn(null);

        $this->normalize($attribute)->shouldReturn(
            [
                'code'                   => 'attribute_size',
                'type'                   => 'Yes/No',
                'group'                  => 'size',
                'unique'                 => false,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => [],
                'metric_family'          => null,
                'default_metric_unit'    => null,
                'reference_data_name'    => null,
                'available_locales'      => [],
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
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
            ]
        );
    }


    function it_normalizes_attribute(
        $transNormalizer,
        $dateTimeNormalizer,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $transNormalizer->normalize(Argument::cetera())->willReturn([]);

        $dateMin = new \DateTime('2015-05-23 15:55:50');
        $dateMax = new \DateTime('2015-06-23 15:55:50');

        $attribute->getType()->willReturn('Yes/No');
        $attribute->getCode()->willReturn('attribute_size');
        $attribute->getGroup()->willReturn($attributeGroup);
        $attributeGroup->getCode()->willReturn('size');
        $attribute->isUnique()->willReturn(true);
        $attribute->isUseableAsGridFilter()->willReturn(true);
        $attribute->getAllowedExtensions()->willReturn(['csv', 'xml', 'standard']);
        $attribute->getMetricFamily()->willReturn('Length');
        $attribute->getDefaultMetricUnit()->willReturn('Centimenter');
        $attribute->getReferenceDataName()->willReturn('color');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getAvailableLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $attribute->getMaxCharacters()->willReturn(255);
        $attribute->getValidationRule()->willReturn('email');
        $attribute->getValidationRegexp()->willReturn('[0-9]*');
        $attribute->isWysiwygEnabled()->willReturn(true);
        $attribute->getNumberMin()->willReturn('0.55');
        $attribute->getNumberMax()->willReturn('1500.55');
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->isNegativeAllowed()->willReturn(true);
        $attribute->getDateMin()->willReturn($dateMin);
        $attribute->getDateMax()->willReturn($dateMax);
        $attribute->getMaxFileSize()->willReturn(1024);
        $attribute->getMinimumInputLength()->willReturn(2);
        $attribute->getSortOrder()->willReturn(4);
        $attribute->getProperty('auto_option_sorting')->willReturn(true);

        $dateTimeNormalizer->normalize($dateMin)->willReturn('2015-05-23T15:55:50+01:00');
        $dateTimeNormalizer->normalize($dateMax)->willReturn('2015-06-23T15:55:50+01:00');

        $this->normalize($attribute)->shouldReturn(
            [
                'code'                   => 'attribute_size',
                'type'                   => 'Yes/No',
                'group'                  => 'size',
                'unique'                 => true,
                'useable_as_grid_filter' => true,
                'allowed_extensions'     => ['csv', 'xml', 'standard'],
                'metric_family'          => 'Length',
                'default_metric_unit'    => 'Centimenter',
                'reference_data_name'    => 'color',
                'available_locales'      => ['en_US', 'fr_FR'],
                'max_characters'         => 255,
                'validation_rule'        => 'email',
                'validation_regexp'      => '[0-9]*',
                'wysiwyg_enabled'        => true,
                'number_min'             => '0.55',
                'number_max'             => '1500.55',
                'decimals_allowed'       => true,
                'negative_allowed'       => true,
                'date_min'               => '2015-05-23T15:55:50+01:00',
                'date_max'               => '2015-06-23T15:55:50+01:00',
                'max_file_size'          => '1024',
                'minimum_input_length'   => 2,
                'sort_order'             => 4,
                'localizable'            => true,
                'scopable'               => true,
                'labels'                 => [],
                'auto_option_sorting'    => true,
            ]
        );
    }
}

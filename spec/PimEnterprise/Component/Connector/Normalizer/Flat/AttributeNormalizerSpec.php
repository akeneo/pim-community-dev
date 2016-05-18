<?php

namespace spec\PimEnterprise\Component\Connector\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Component\Connector\Normalizer\Flat\AttributeNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(AttributeNormalizer $attributeNormalizer) {
        $this->beConstructedWith($attributeNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Connector\Normalizer\Flat\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes_attribute($attributeNormalizer, AttributeInterface $attribute)
    {
        $attribute->getProperty('is_read_only')->willReturn(false);

        $attributeNormalizer->normalize($attribute, 'json', [])->willReturn(
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
                'max_characters'         => '',
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => '',
                'number_min'             => '',
                'number_max'             => '',
                'decimals_allowed'       => '',
                'negative_allowed'       => '',
                'date_min'               => '',
                'date_max'               => '',
                'max_file_size'          => '',
                'minimum_input_length'   => '',
                'sort_order'             => 0,
                'localizable'            => 1,
                'scopable'               => 0,
            ]
        );

        $this->normalize($attribute, 'json', [])->shouldReturn(
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
                'max_characters'         => '',
                'validation_rule'        => '',
                'validation_regexp'      => '',
                'wysiwyg_enabled'        => '',
                'number_min'             => '',
                'number_max'             => '',
                'decimals_allowed'       => '',
                'negative_allowed'       => '',
                'date_min'               => '',
                'date_max'               => '',
                'max_file_size'          => '',
                'minimum_input_length'   => '',
                'sort_order'             => 0,
                'localizable'            => 1,
                'scopable'               => 0,
                'is_read_only'           => 0,
            ]
        );
    }
}

<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_adds_the_attribute_id_to_the_normalized_attribute($normalizer, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn(
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
                'max_file_size'         => '',
                'sort_order'            => 2,
                'group_code'            => null,
                'group'                 => null,
            ]
        );
        $price->getProperty('is_read_only')->willReturn(true);

        $this->normalize($price, 'json', [])->shouldReturn(
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
                'max_file_size'         => '',
                'sort_order'            => 2,
                'group_code'            => null,
                'group'                 => null,
                'is_read_only'          => true,
            ]
        );
    }
}

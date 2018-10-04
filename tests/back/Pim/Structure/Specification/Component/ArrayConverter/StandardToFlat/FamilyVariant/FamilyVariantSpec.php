<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant\FamilyVariant;

class FamilyVariantSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariant::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_a_family_variant_from_standard_to_flat_format()
    {
        $expected = [
            'code' => 'family_variant_code',
            'family' => 'family_code',
            'label-en_US' => 'My family variant',
            'label-fr_FR' => 'Ma variante de famille',
            'variant-axes_1' => 'a_simple_select,a_reference_data',
            'variant-attributes_1' => 'an_attribute,another_attribute,yet_another_attribute',
            'variant-axes_2' => 'a_boolean',
            'variant-attributes_2' => 'an_identifier',
        ];

        $item = [
            'code' => 'family_variant_code',
            'family' => 'family_code',
            'labels' => [
                'en_US' => 'My family variant',
                'fr_FR' => 'Ma variante de famille',
            ],
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select', 'a_reference_data'],
                    'attributes' => ['an_attribute', 'another_attribute', 'yet_another_attribute'],
                ],
                [
                    'level' => 2,
                    'axes' => ['a_boolean'],
                    'attributes' => ['an_identifier'],
                ],
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}

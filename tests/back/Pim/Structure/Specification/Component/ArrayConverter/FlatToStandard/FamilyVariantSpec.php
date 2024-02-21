<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\FamilyVariant;

class FamilyVariantSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldsChecker)
    {
        $this->beConstructedWith($fieldsChecker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariant::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_from_flat_to_standard_format()
    {
        $item = [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'label-fr_FR' => 'Mon tshirt',
            'label-en_US' => 'My tshirt',
            'variant-axes_1' => 'color',
            'variant-attributes_1' => 'description',
            'variant-axes_2' => 'size,other',
            'variant-attributes_2' => 'size,other,sku',
        ];

        $expected = [
            'labels' => [
                'fr_FR' => 'Mon tshirt',
                'en_US' => 'My tshirt',
            ],
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['description'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'other', 'sku'],
                ]
            ],
            'code' => 'my-tshirt',
            'family' => 't-shirt',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}

<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard\SelectOptionDetails;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\ArrayConversionException;
use PhpSpec\ObjectBehavior;

class SelectOptionDetailsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new FieldsRequirementChecker());
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
        $this->shouldHaveType(SelectOptionDetails::class);
    }

    function it_converts_select_option_details_from_flat_to_standard()
    {
        $this->convert(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'salt',
                'label-en_US' => 'Salt',
                'label-fr_FR' => 'Sel',
            ]
        )->shouldReturn(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'salt',
                'labels' => [
                    'en_US' => 'Salt',
                    'fr_FR' => 'Sel',
                ],
            ]
        );
    }

    function it_should_throw_an_exception_if_code_is_empty()
    {
        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'attribute' => 'nutrition',
                    'column' => 'ingredient',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );

        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'attribute' => 'nutrition',
                    'column' => 'ingredient',
                    'code' => '',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );
    }

    function it_does_not_convert_labels_if_they_are_not_set()
    {
        $this->convert(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'salt',
            ]
        )->shouldReturn(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'salt',
            ]
        );
    }

    function it_should_throw_an_exception_if_attribute_is_empty()
    {
        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'column' => 'ingredient',
                    'code' => 'salt',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );

        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'attribute' => '',
                    'column' => 'ingredient',
                    'code' => 'salt',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );
    }

    function it_should_throw_an_exception_if_column_is_empty()
    {
        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'attribute' => 'nutrition',
                    'code' => 'salt',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );

        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [
                [
                    'attribute' => 'nutrition',
                    'column' => '',
                    'code' => 'salt',
                    'label-en_US' => 'Salt',
                    'label-fr_FR' => 'Sel',
                ],
            ]
        );
    }
}

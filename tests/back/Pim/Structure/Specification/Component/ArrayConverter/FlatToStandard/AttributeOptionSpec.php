<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AttributeOptionSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($localeRepository, $fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            ArrayConverterInterface::class
        );
    }

    function it_converts_an_item_to_standard_format($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE', 'en_US', 'fr_FR']);

        $this->convert(
            [
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => '2',
                'label-de_DE' => '210 x 1219 mm',
                'label-en_US' => '210 x 1219 mm',
                'label-fr_FR' => '210 x 1219 mm'
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => 2
            ]
        );
    }

    function it_throws_exception_when_the_attribute_field_is_missing($fieldChecker)
    {
        $item = [
            'code'         => '210_x_1219_mm',
            'sort_order'   => '2',
            'label-de_DE'  => '210 x 1219 mm',
            'label-en_US'  => '210 x 1219 mm',
            'label-fr_FR'  => '210 x 1219 mm',
        ];

        $fieldChecker
            ->checkFieldsPresence($item, ['attribute', 'code'])
            ->willThrow(StructureArrayConversionException::class);

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during('convert', [$item]);
    }

    function it_throws_exception_when_unauthorized_field_is_provided($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE', 'en_US', 'fr_FR']);

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during(
                'convert',
                [
                    [
                        'attribute'    => 'maximum_print_size',
                        'code'         => '210_x_1219_mm',
                        'sort_order'   => '2',
                        'label-de_DE'  => '210 x 1219 mm',
                        'label-en_US'  => '210 x 1219 mm',
                        'label-fr_FR'  => '210 x 1219 mm',
                        'unknow_field' => 'My data'
                    ]
                ]
            );
    }

    function it_converts_an_item_without_adding_a_sort_order_value($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE', 'en_US', 'fr_FR']);

        $this->convert(
            [
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'label-de_DE' => '210 x 1219 mm',
                'label-en_US' => '210 x 1219 mm',
                'label-fr_FR' => '210 x 1219 mm'
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm'
            ]
        );
    }
}

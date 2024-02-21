<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\GetCaseSensitiveLocaleCodeInterface;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedLocalesInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Prophecy\Argument;

class AttributeOptionSpec extends ObjectBehavior
{
    function let(
        FindLocales $findLocales,
        FieldsRequirementChecker $fieldChecker
    ): void {
        $enUS = new Locale('en_US', true);
        $frFR = new Locale('fr_FR', true);
        $deDE = new Locale('de_DE', false);

        $findLocales->find(Argument::that(static fn ($arg): bool => \strtolower($arg) === 'en_us'))->willReturn($enUS);
        $findLocales->find(Argument::that(static fn ($arg): bool => \strtolower($arg) === 'fr_fr'))->willReturn($frFR);
        $findLocales->find(Argument::that(static fn ($arg): bool => \strtolower($arg) === 'de_de'))->willReturn($deDE);
        $findLocales->find(Argument::any())->willReturn(null);

        $findLocales->findAllActivated()->willReturn([$enUS, $frFR]);

        $this->beConstructedWith($findLocales, $fieldChecker);
    }

    function it_is_a_standard_array_converter(): void
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_an_item_to_standard_format(): void
    {
        $this->convert(
            [
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => '2',
                'label-en_US' => '210 x 1219 mm',
                'label-fr_FR' => '210 x 1219 mm'
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => 2
            ]
        );
    }

    function it_fixes_locale_codes_case(): void
    {
        $this->convert(
            [
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => '2',
                'label-EN_US' => '210 x 1219 mm',
                'label-FR_fr' => '210 x 1219 mm'
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => 2
            ]
        );
    }

    function it_throws_exception_when_the_attribute_field_is_missing($fieldChecker): void
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

    function it_throws_an_exception_when_unauthorized_field_is_provided(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during(
                'convert',
                [
                    [
                        'attribute' => 'maximum_print_size',
                        'code' => '210_x_1219_mm',
                        'unknown_field' => 'My data'
                    ]
                ]
            );
    }

    function it_throws_an_exception_when_a_locale_is_inactive(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during(
                'convert',
                [
                    [
                        'attribute' => 'maximum_print_size',
                        'code' => '210_x_1219_mm',
                        'label-de_DE' => 'My data'
                    ]
                ]
            );
    }

    function it_throws_an_exception_when_a_locale_is_unknown(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during(
                'convert',
                [
                    [
                        'attribute' => 'maximum_print_size',
                        'code' => '210_x_1219_mm',
                        'label-unknown_LOCALE' => 'My data'
                    ]
                ]
            );
    }

    function it_converts_an_item_without_adding_a_sort_order_value(): void
    {
        $this->convert(
            [
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'label-en_US' => '210 x 1219 mm',
                'label-fr_FR' => '210 x 1219 mm'
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm'
            ]
        );
    }
}

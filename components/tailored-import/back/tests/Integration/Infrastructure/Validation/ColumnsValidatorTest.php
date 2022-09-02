<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\Columns;
use Akeneo\Test\Integration\Configuration;

final class ColumnsValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validColumns
     */
    public function test_it_does_not_build_violations_when_columns_are_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Columns());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidColumns
     */
    public function test_it_build_violations_when_columns_are_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Columns());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validColumns(): array
    {
        return [
            'valid columns' => [
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'index' => 0,
                        'label' => 'Sku'
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd1',
                        'index' => 1,
                        'label' => 'Name'
                    ]
                ],
            ],
        ];
    }

    public function invalidColumns(): array
    {
        return [
            'empty column label' => [
                'akeneo.tailored_import.validation.columns.label.should_not_be_blank',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][label]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'index' => 0,
                        'label' => '',
                    ]
                ]
            ],
            'too long column label' => [
                'akeneo.tailored_import.validation.columns.label.max_length_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][label]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'index' => 0,
                        'label' => str_repeat('a', 256),
                    ]
                ]
            ],
            'duplicate column uuid' => [
                'akeneo.tailored_import.validation.columns.uuid.should_be_unique',
                '[72bdf3c7-5647-427b-be62-e3e560c0eb45][uuid]',
                [
                    [
                        'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                        'index' => 0,
                        'label' => 'Sku'
                    ],
                    [
                        'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                        'index' => 1,
                        'label' => 'Name'
                    ],
                ],
            ],
            'duplicate column index' => [
                'akeneo.tailored_import.validation.columns.index.should_be_unique',
                '[72bdf3c7-5647-427b-be62-e3e560c0eb45][index]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'index' => 0,
                        'label' => 'Sku'
                    ],
                    [
                        'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                        'index' => 0,
                        'label' => 'Name'
                    ],
                ],
            ],
            'too many columns' => [
                'akeneo.tailored_import.validation.columns.max_count_reached',
                '',
                array_fill(0, 501, [
                    'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                    'index' => 0,
                    'label' => 'Sku'
                ]),
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

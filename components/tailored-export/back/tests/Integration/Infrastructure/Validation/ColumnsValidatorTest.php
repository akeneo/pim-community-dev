<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Columns;
use Akeneo\Test\Integration\Configuration;

final class ColumnsValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validColumns
     */
    public function test_it_does_not_build_violations_when_attribute_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Columns());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidColumns
     */
    public function test_it_build_violations_when_attribute_is_invalid(
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
            'a valid column' => [
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => 'column_1',
                        'sources' => [
                            [
                                'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                'code' => 'a_text',
                                'type' => 'attribute',
                                'locale' => null,
                                'channel' => null,
                                'operations' => [],
                                'selection' => [
                                    'type' => 'code'
                                ],
                            ],
                        ],
                        'format' => [
                            'type' => 'concat',
                            'space_between' => true,
                            'elements' => [
                                [
                                    'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                    'type' => 'source',
                                    'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidColumns(): array
    {
        return [
            'empty column name' => [
                'akeneo.tailored_export.validation.columns.target.should_not_be_blank',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][target]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => '',
                        'sources' => [
                            [
                                'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                'code' => 'a_text',
                                'type' => 'attribute',
                                'locale' => null,
                                'channel' => null,
                                'operations' => [],
                                'selection' => [
                                    'type' => 'code'
                                ],
                            ],
                        ],
                        'format' => [
                            'type' => 'concat',
                            'space_between' => true,
                            'elements' => [
                                [
                                    'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                    'type' => 'source',
                                    'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'too long column name' => [
                'akeneo.tailored_export.validation.columns.target.max_length_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][target]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => str_repeat('m', 256),
                        'sources' => [
                            [
                                'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                'code' => 'a_text',
                                'type' => 'attribute',
                                'locale' => null,
                                'channel' => null,
                                'operations' => [],
                                'selection' => [
                                    'type' => 'code'
                                ],
                            ],
                        ],
                        'format' => [
                            'type' => 'concat',
                            'space_between' => true,
                            'elements' => [
                                [
                                    'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                    'type' => 'source',
                                    'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'duplicate column name' => [
                'akeneo.tailored_export.validation.columns.target.should_be_unique',
                '[52bdf3c7-5647-427b-be62-e3e560c0eb45][target]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => 'column_1',
                        'sources' => [
                            [
                                'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                'code' => 'a_text',
                                'type' => 'attribute',
                                'locale' => null,
                                'channel' => null,
                                'operations' => [],
                                'selection' => [
                                    'type' => 'code'
                                ],
                            ],
                        ],
                        'format' => [
                            'type' => 'concat',
                            'space_between' => true,
                            'elements' => [
                                [
                                    'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                    'type' => 'source',
                                    'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                ],
                            ],
                        ],
                    ],
                    [
                        'uuid' => '52bdf3c7-5647-427b-be62-e3e560c0eb45',
                        'target' => 'column_1',
                        'sources' => [
                            [
                                'uuid' => '118e1a5e-4d77-4a15-add8-f142111d4cd0',
                                'code' => 'a_text',
                                'type' => 'attribute',
                                'locale' => null,
                                'channel' => null,
                                'operations' => [],
                                'selection' => [
                                    'type' => 'code'
                                ],
                            ],
                        ],
                        'format' => [
                            'type' => 'concat',
                            'space_between' => true,
                            'elements' => [
                                [
                                    'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                    'type' => 'source',
                                    'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'too much columns' => [
                'akeneo.tailored_export.validation.columns.max_column_count_reached',
                '',
                array_fill(0, 1001, [
                    'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                    'target' => '',
                    'sources' => [
                        [
                            'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                            'code' => 'a_text',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [],
                            'selection' => [
                                'type' => 'code'
                            ],
                        ],
                    ],
                    'format' => [
                        'type' => 'concat',
                        'space_between' => true,
                        'elements' => [
                            [
                                'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                                'type' => 'source',
                                'value' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                            ],
                        ],
                    ],
                ]),
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

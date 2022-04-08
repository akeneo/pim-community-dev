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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMappings;
use Akeneo\Test\Integration\Configuration;

final class DataMappingsValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function test_it_does_not_build_violations_when_data_mappings_are_valid(array $value): void
    {
        $columns = $this->getColumns();
        $violations = $this->getValidator()->validate($value, new DataMappings($columns));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidDataMappings
     */
    public function test_it_build_violations_when_data_mappings_are_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $columns = $this->getColumns();
        $violations = $this->getValidator()->validate($value, new DataMappings($columns));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    /**
     * @dataProvider invalidDataMappingStructures
     */
    public function test_it_throw_an_exception_when_data_mapping_structures_is_invalid(array $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $columns = $this->getColumns();
        $this->getValidator()->validate($value, new DataMappings($columns));
    }

    public function validDataMappings(): array
    {
        return [
            'valid data mappings' => [
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => null,
                            'locale' => null,
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd1',
                        'target' => [
                            'code' => 'categories',
                            'type' => 'property',
                            'action_if_not_empty' => 'add',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ],
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'too many data mappings' => [
                'akeneo.tailored_import.validation.data_mappings.max_count_reached',
                '',
                array_fill(0, 501, [
                    'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                    'target' => [
                        'code' => 'sku',
                        'type' => 'attribute',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_parameter' => null,
                    ],
                    'sources' => [
                        '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                    ],
                    'operations' => [],
                    'sample_data' => [],
                ]),
            ],
            'duplicate data mapping uuid' => [
                'akeneo.tailored_import.validation.data_mappings.uuid.should_be_unique',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][uuid]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => null,
                            'locale' => null,
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'categories',
                            'type' => 'property',
                            'action_if_not_empty' => 'add',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ],
            ],
            'data mapping without identifier target' => [
                'akeneo.tailored_import.validation.data_mappings.no_identifier_target_found',
                '',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'categories',
                            'type' => 'property',
                            'action_if_not_empty' => 'add',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ]
            ],
            'data mappings with too many identifier target' => [
                'akeneo.tailored_import.validation.data_mappings.too_many_identifier_target_found',
                '',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => null,
                            'locale' => null,
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd9',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => null,
                            'locale' => null,
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                            'source_parameter' => null,
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ],
            ],
        ];
    }

    public function invalidDataMappingStructures(): array
    {
        return [
            'data mapping with missing uuid' => [
                [
                    [
                        'target' => [
                            'code' => 'a_localized_and_scopable_text_area',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                ],
            ],
            'data mapping with missing target' => [
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getColumns(): array
    {
        return [
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
             '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
        ];
    }
}

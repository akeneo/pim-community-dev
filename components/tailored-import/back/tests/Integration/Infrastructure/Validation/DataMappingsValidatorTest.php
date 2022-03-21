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
            'data mapping with empty sources for single source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'a_localized_and_scopable_text_area',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'sources' => [],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ]
            ],
            'data mapping with empty sources for a multi source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.min_count_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'categories',
                            'type' => 'property',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'sources' => [],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ]
            ],
            'data mapping with too many sources for single source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'a_localized_and_scopable_text_area',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
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
            'data mapping with too many sources for a multi source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'categories',
                            'type' => 'property',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ]
            ],
            'data mapping with duplicated sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'a_localized_and_scopable_text_area',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ]
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
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
                ],
            ],
            'data mapping with missing uuid' => [
                'This field is missing.',
                '[null][uuid]',
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
            'data mapping with invalid uuid' => [
                'This is not a valid UUID.',
                '[invalid][uuid]',
                [
                    [
                        'uuid' => 'invalid',
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
                'This field is missing.',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][target]',
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
            'data mapping with missing sources' => [
                'This field is missing.',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'a_localized_and_scopable_text_area',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action_if_not_empty' => 'set',
                            'action_if_empty' => 'skip',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ],
                ],
            ],
            'data mapping with missing operations' => [
                'This field is missing.',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][operations]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
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
                        'sample_data' => [],
                    ],
                ],
            ],
            'data mapping with missing sample_data' => [
                'This field is missing.',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sample_data]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
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
                    ],
                ],
            ],
            'data mapping with invalid sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.should_exist',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd1][sources]',
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
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fg04',
                        ],
                        'operations' => [],
                        'sample_data' => [],
                    ]
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
            [
                'uuid' => '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                'index' => 0,
                'label' => 'Label 1',
            ],
            [
                'uuid' => '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                'index' => 1,
                'label' => 'Label 2',
            ],
            [
                'uuid' => '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                'index' => 2,
                'label' => 'Label 3',
            ],
            [
                'uuid' =>  '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
                'index' => 3,
                'label' => 'Label 4',
            ],
            [
                'uuid' => '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
                'index' => 4,
                'label' => 'Label 5',
            ],
            [
                'uuid' => '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
                'index' => 5,
                'label' => 'Label 5',
            ],
        ];
    }
}

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
        $violations = $this->getValidator()->validate($value, new DataMappings());

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
        $violations = $this->getValidator()->validate($value, new DataMappings());

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
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd1',
                        'target' => [
                            'code' => 'category',
                            'type' => 'property',
                            'action' => 'add',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ]
                ],
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'duplicate data mapping uuid' => [
                'akeneo.tailored_import.validation.data_mappings.uuid.should_be_unique',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][uuid]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'category',
                            'type' => 'property',
                            'action' => 'add',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc03',
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ]
                ],
            ],
            'data mapping with empty sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.min_count_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',
                        ],
                        'sources' => [],
                        'operations' => [],
                        'sampleData' => [],
                    ]
                ]
            ],
            'data mapping with too many sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ]
                ]
            ],
            'data mapping with duplicate sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique',
                '[018e1a5e-4d77-4a15-add8-f142111d4cd0][sources]',
                [
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                        ],
                        'operations' => [],
                        'sampleData' => [],
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
                            'code' => 'category',
                            'type' => 'property',
                            'action' => 'add',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',
                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                        ],
                        'operations' => [],
                        'sampleData' => [],
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
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ],
                    [
                        'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd9',
                        'target' => [
                            'code' => 'sku',
                            'type' => 'attribute',
                            'channel' => 'ecommerce',
                            'locale' => 'en_US',
                            'action' => 'set',
                            'ifEmpty' => 'skip',
                            'onError' => 'skipLine',

                        ],
                        'sources' => [
                            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc63',
                        ],
                        'operations' => [],
                        'sampleData' => [],
                    ]
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

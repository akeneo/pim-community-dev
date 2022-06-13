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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping\Target\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Number\Number;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class NumberValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function test_it_does_not_build_violations_when_data_mapping_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Number([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
        ], $this->getAttribute()));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidDataMappings
     */
    public function test_it_build_violations_when_data_mapping_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Number([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
        ], $this->getAttribute()));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validDataMappings(): array
    {
        return [
            'a valid number attribute target' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => [
                            'decimal_separator' => '.',
                        ]
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid number attribute target with clear value' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                        'source_configuration' => [
                            'decimal_separator' => '.',
                        ]
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid attribute target with sample data' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => [
                            'decimal_separator' => ',',
                        ]
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['1', 'sample_2', 'sample_3'],
                ]
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'an number data mapping with an invalid uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'an_invalid_uuid',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping does not handle add action if not empty' => [
                'This value should be equal to "set".',
                '[target][action_if_not_empty]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'add',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping with an invalid decimal separator' => [
                'The value you selected is not a valid choice.',
                '[target][source_configuration][decimal_separator]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => [
                            'decimal_separator' => '|',
                        ]
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping with an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.incompatible_operation_type',
                '[operations][0][type]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'type' => 'clean_html_tags'
                        ],
                    ],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping should have a source' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => [],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping cannot have multiple sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529', '71480f22-f811-4261-b0fe-d93ad11666a9'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'an number data mapping with an invalid sample data' => [
                'This value should be of type string.',
                '[sample_data][0]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_number_float',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_number',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [12],
                ]
            ],
        ];
    }

    private function getAttribute(): Attribute
    {
        return new Attribute(
            'a_number_float',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            null,
            false,
            'decimal',
            [],
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

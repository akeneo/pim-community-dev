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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping\Target\Property;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Property\Categories\Categories;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class CategoriesValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function test_it_does_not_build_violations_when_data_mapping_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Categories([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
        ]));

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
        $violations = $this->getValidator()->validate($value, new Categories([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
            '71480f22-f811-4261-b0fe-d93ad11666a8',
            '71480f22-f811-4261-b0fe-d93ad11666a7',
            '71480f22-f811-4261-b0fe-d93ad11666a6',
        ]));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validDataMappings(): array
    {
        return [
            'a valid categories data mapping' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid categories data mapping with clear value' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid categories data mapping with add action if not empty' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'add',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid categories data mapping with sample data' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['categorie_1', 'categorie_2', 'categorie_3'],
                ]
            ],
            'a valid categories data mapping with split operation' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'type' => 'split',
                            'separator' => ';',
                        ]
                    ],
                    'sample_data' => [],
                ]
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'a categories data mapping with an invalid uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'an_invalid_uuid',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'a categories data mapping with an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.incompatible_operation_type',
                '[operations][0][type]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
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
            'a categories data mapping should have a source' => [
                'akeneo.tailored_import.validation.data_mappings.sources.at_least_one_required',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => [],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'a categories data mapping cannot have more than 4 sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => [
                        '7fa661ce-3a6c-4b95-8441-259911b70529',
                        '71480f22-f811-4261-b0fe-d93ad11666a9',
                        '71480f22-f811-4261-b0fe-d93ad11666a8',
                        '71480f22-f811-4261-b0fe-d93ad11666a7',
                        '71480f22-f811-4261-b0fe-d93ad11666a6',
                    ],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'a categories data mapping with an invalid sample data' => [
                'This value should be of type string.',
                '[sample_data][0]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [12],
                ]
            ],
            'a categories data mapping with a channel' => [
                'This field was not expected.',
                '[target][channel]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'channel' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a categories data mapping with a locale' => [
                'This field was not expected.',
                '[target][locale]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a categories data mapping with a source configuration' => [
                'This field was not expected.',
                '[target][source_configuration]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

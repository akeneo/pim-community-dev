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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Property\Family\Family;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class FamilyValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function test_it_does_not_build_violations_when_data_mapping_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Family([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
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
        $violations = $this->getValidator()->validate($value, new Family([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '7fa661ce-3a6c-4b95-8441-259911b70530',
        ]));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validDataMappings(): array
    {
        return [
            'a valid family data mapping' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'a family data mapping with an invalid uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'an_invalid_uuid', // <== Here is the error
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping with an invalid target action if not empty' => [
                'This value should be equal to "set".',
                '[target][action_if_not_empty]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'add', // <== Here is the error
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping with an invalid target action if empty' => [
                'This value should be equal to "skip".',
                '[target][action_if_empty]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'add',
                        'action_if_empty' => 'clear', // <== Here is the error
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping should have a source' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'categories',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => [], // <== Here is the error
                    'operations' => [],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping with multiple sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => [
                        '7fa661ce-3a6c-4b95-8441-259911b70529',
                        '7fa661ce-3a6c-4b95-8441-259911b70530', // <== Here is the error
                    ],
                    'operations' => [],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping with an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.incompatible_operation_type',
                '[operations][0][type]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'type' => 'clean_html_tags' // <== Here is the error
                        ],
                    ],
                    'sample_data' => ['a_family', 'another_family'],
                ]
            ],
            'a family data mapping with an invalid sample data' => [
                'This value should be of type string.',
                '[sample_data][0]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [12], // <== Here is the error
                ]
            ],
            'a family data mapping with a channel' => [
                'This field was not expected.',
                '[target][channel]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'family',
                        'type' => 'property',
                        'channel' => null, // <== Here is the error
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
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

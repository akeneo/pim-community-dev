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
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\MultiReferenceEntity\MultiReferenceEntity;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class MultiReferenceEntityValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function test_it_does_not_build_violations_when_data_mapping_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new MultiReferenceEntity([
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
        $violations = $this->getValidator()->validate($value, new MultiReferenceEntity([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
            '71480f22-f811-4261-b0fe-d93ad11666a8',
            '71480f22-f811-4261-b0fe-d93ad11666a7',
            '71480f22-f811-4261-b0fe-d93ad11666a6',
        ], $this->getAttribute()));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validDataMappings(): array
    {
        return [
            'a valid multi reference entity data mapping' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid multi reference entity data mapping with clear value' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid multi reference entity data mapping with add action if not empty' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'add',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ]
            ],
            'a valid multi reference entity data mapping with sample data' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'a valid multi reference entity data mapping with split operation' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                            'type' => 'split',
                            'separator' => ',',
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
            'a multi reference entity data mapping with an invalid uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'an_invalid_uuid',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
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
            'a multi reference entity data mapping with an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.incompatible_operation_type',
                '[operations][0][type]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'type' => 'clean_html'
                        ],
                    ],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ]
            ],
            'a multi reference entity data mapping should have a source' => [
                'akeneo.tailored_import.validation.data_mappings.sources.at_least_one_required',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
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
            'a multi reference entity data mapping cannot have more than 4 sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null
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
            'a multi reference entity data mapping with an invalid sample data' => [
                'This value should be of type {{ type }}.',
                '[sample_data][0]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'a_multi_reference',
                        'type' => 'attribute',
                        'attribute_type' => 'akeneo_reference_entity_collection',
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
            'a_multi_reference_entity',
            'akeneo_reference_entity_collection',
            [],
            false,
            false,
            null,
            null,
            false,
            'options',
            [],
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

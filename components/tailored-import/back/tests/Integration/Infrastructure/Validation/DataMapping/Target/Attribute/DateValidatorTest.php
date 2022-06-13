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
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Date\Date;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class DateValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validDataMappings
     */
    public function testItDoesNotBuildViolationsWhenDataMappingIsValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Date([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
        ], $this->getAttribute()));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidDataMappings
     */
    public function testItBuildViolationsWhenDataMappingIsInvalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Date([
            '7fa661ce-3a6c-4b95-8441-259911b70529',
            '71480f22-f811-4261-b0fe-d93ad11666a9',
        ], $this->getAttribute()));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validDataMappings(): array
    {
        return [
            'a valid date data mapping' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => ['date_format' => 'mm/dd/yyyy'],
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [],
                ],
            ],
            'a valid boolean data mapping with sample data' => [
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['01/22/2022', '10/30/2022', '11/11/2022'],
                ],
            ],
        ];
    }

    public function invalidDataMappings(): array
    {
        return [
            'a date data mapping with an invalid uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'an_invalid_uuid',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'clear',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ],
            ],
            'a date data mapping does not handle add action if not empty' => [
                'This value should be equal to "set".',
                '[target][action_if_not_empty]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'add',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ],
            ],
            'a date data mapping with an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.operation_type_does_not_exist',
                '[operations][0][type]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [
                        [
                            'type' => 'unknown_operation',
                        ],
                    ],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ],
            ],
            'a date data mapping should have a source' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => [],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ],
            ],
            'a date data mapping cannot have multiple sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '[sources]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529', '71480f22-f811-4261-b0fe-d93ad11666a9'],
                    'operations' => [],
                    'sample_data' => ['sample_1', 'sample_2', 'sample_3'],
                ],
            ],
            'a date data mapping with an invalid sample data' => [
                'This value should be of type string.',
                '[sample_data][0]',
                [
                    'uuid' => 'f3513836-4f1d-4bf6-b1a0-ce85ddcca5cd',
                    'target' => [
                        'code' => 'date',
                        'type' => 'attribute',
                        'attribute_type' => 'pim_catalog_date',
                        'channel' => null,
                        'locale' => null,
                        'action_if_not_empty' => 'set',
                        'action_if_empty' => 'skip',
                        'source_configuration' => null,
                    ],
                    'sources' => ['7fa661ce-3a6c-4b95-8441-259911b70529'],
                    'operations' => [],
                    'sample_data' => [12],
                ],
            ],
        ];
    }

    private function getAttribute(): Attribute
    {
        return new Attribute(
            'date',
            'pim_catalog_date',
            [],
            false,
            false,
            null,
            null,
            false,
            'boolean',
            ['date_format' => 'mm/dd/yyyy'],
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

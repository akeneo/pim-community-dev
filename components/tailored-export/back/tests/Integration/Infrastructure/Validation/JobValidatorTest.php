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

use Akeneo\Test\Integration\Configuration;

class JobValidatorTest extends AbstractValidationTest
{
    public function test_it_validates_a_valid_job(): void
    {
        $job = $this->get('akeneo_batch.job.job_registry')->get('xlsx_tailored_product_export');
        $parameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $this->getValidJobParameters());

        $parametersViolations = $this
            ->get('akeneo_batch.job.job_parameters_validator')
            ->validate($job, $parameters, null);

        $this->assertCount(0, $parametersViolations);
    }

    public function test_it_invalidates_an_invalid_job(): void
    {
        $job = $this->get('akeneo_batch.job.job_registry')->get('xlsx_tailored_product_export');
        $parameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $this->getInvalidJobParameters());

        $parametersViolations = $this
            ->get('akeneo_batch.job.job_parameters_validator')
            ->validate($job, $parameters, null);

        $this->assertCount(3, $parametersViolations);
        $this->assertHasValidationError(
            'akeneo.tailored_export.validation.attribute.should_exist',
            '[columns][018e1a5e-4d77-4a15-add8-f142111d4cd0][sources][1e65c742-da0b-4508-9014-86fe70df2f8b]',
            $parametersViolations,
        );

        $this->assertHasValidationError(
            'This value should be equal to {{ compared_value }}.',
            '[columns][018e1a5e-4d77-4a15-add8-f142111d4cd0][sources][72bdf3c7-5647-427b-be62-e3e560c0eb45][selection][type]',
            $parametersViolations,
        );

        $this->assertHasValidationError(
            'This value should be of type {{ type }}.',
            '[columns][018e1a5e-4d77-4a15-add8-f142111d4cd0][sources][1f6017dc-d844-499e-ae06-d7adadeb499b][operations][default_value][value]',
            $parametersViolations,
        );
    }

    private function getValidJobParameters(): array
    {
        return [
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/export_%job_label%_%datetime%.xlsx',
            ],
            'withHeader' => true,
            'linesPerFile' => 10000,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
            'with_media' => true,
            'columns' => [
                [
                    'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                    'target' => 'column_1',
                    'sources' => [
                        [
                            'uuid' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                            'code' => 'a_metric',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [
                                'measurement_conversion' => [
                                    'type' => 'measurement_conversion',
                                    'target_unit_code' => 'MEGAWATT',
                                ],
                            ],
                            'selection' => [
                                'type' => 'unit_code'
                            ],
                        ],
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
                        [
                            'uuid' => '1f6017dc-d844-499e-ae06-d7adadeb499b',
                            'code' => 'a_text',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [
                                'default_value' => [
                                    'type' => 'default_value',
                                    'value' => 'foo',
                                ],
                            ],
                            'selection' => [
                                'type' => 'code'
                            ]
                        ]
                    ],
                    'format' => [
                        'type' => 'concat',
                        'space_between' => true,
                        'elements' => [
                            [
                                'uuid' => 'd494e3cb-cffb-4e9a-bcd8-1cd41203529d',
                                'type' => 'text',
                                'value' => 'foo',
                            ],
                            [
                                'uuid' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                                'type' => 'source',
                                'value' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }

    private function getInvalidJobParameters(): array
    {
        return [
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/export_%job_label%_%datetime%.xlsx',
            ],
            'withHeader' => true,
            'linesPerFile' => 10000,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
            'with_media' => true,
            'columns' => [
                [
                    'uuid' => '018e1a5e-4d77-4a15-add8-f142111d4cd0',
                    'target' => 'column_1',
                    'sources' => [
                        [
                            'uuid' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                            'code' => 'an_unknown_attribute',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [],
                        ],
                        [
                            'uuid' => '72bdf3c7-5647-427b-be62-e3e560c0eb45',
                            'code' => 'a_text',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [],
                            'selection' => [
                                'type' => 'label'
                            ],
                        ],
                        [
                            'uuid' => '1f6017dc-d844-499e-ae06-d7adadeb499b',
                            'code' => 'a_text',
                            'type' => 'attribute',
                            'locale' => null,
                            'channel' => null,
                            'operations' => [
                                'default_value' => [
                                    'type' => 'default_value',
                                    'value' => 123,
                                ],
                            ],
                            'selection' => [
                                'type' => 'code'
                            ]
                        ]
                    ],
                    'format' => [
                        'type' => 'concat',
                        'space_between' => true,
                        'elements' => [
                            [
                                'uuid' => 'd494e3cb-cffb-4e9a-bcd8-1cd41203529d',
                                'type' => 'text',
                                'value' => 'foo',
                            ],
                            [
                                'uuid' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                                'type' => 'source',
                                'value' => '1e65c742-da0b-4508-9014-86fe70df2f8b',
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

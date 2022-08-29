<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Scheduling;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateSchedulingTest extends AbstractValidationTest
{
    /**
     * @dataProvider validScheduledJobInstance
     */
    public function test_it_does_not_build_violations_when_scheduled_job_instance_is_valid(array $normalizedValue): void
    {
        $value = $this->createJobInstance($normalizedValue);
        $violations = $this->getValidator()->validate($value, new Scheduling());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidScheduledJobInstance
     */
    public function test_it_builds_violations_when_scheduled_job_instance_is_invalid(
        array $normalizedValue,
        string $expectedErrorMessage,
        string $expectedErrorPath,
    ): void {
        $value = $this->createJobInstance($normalizedValue);
        $violations = $this->getValidator()->validate($value, new Scheduling());

        $this->assertHasValidationError(
            $expectedErrorMessage,
            $expectedErrorPath,
            $violations,
        );
    }

    public function validScheduledJobInstance(): array
    {
        return [
            'valid scheduled export job instance without storage' => [
                [
                    'scheduled' => true,
                    'type' => 'export',
                    'raw_parameters' => [
                        'storage' => [
                            'type' => 'none'
                        ],
                        'delimiter' => ';',
                    ],
                    'notified_users' => [],
                    'notified_user_groups' => [],
                ],
            ],
            'valid scheduled import job instance with storage' => [
                [
                    'scheduled' => true,
                    'type' => 'import',
                    'raw_parameters' => [
                        'storage' => [
                            'type' => 'local',
                            'file_path' => 'a_path.xlsx'
                        ],
                    ],
                    'notified_users' => [],
                    'notified_user_groups' => [],
                ],
            ],
        ];
    }

    public function invalidScheduledJobInstance(): array
    {
        return [
            'invalid scheduled import job without storage' => [
                [
                    'scheduled' => true,
                    'type' => 'import',
                    'raw_parameters' => [
                        'storage' => [
                            'type' => 'none'
                        ],
                    ],
                    'notified_users' => [],
                    'notified_user_groups' => [],
                ],
                'No storage has been configured in the Properties tab of your import profile.',
                '[scheduled]',
            ],
        ];
    }

    private function createJobInstance(array $normalizedJobInstance): JobInstance
    {
        $jobInstance = new JobInstance(
            'my_connector',
            $normalizedJobInstance['type'],
            'my_job',
        );

        $jobInstance->setScheduled($normalizedJobInstance['scheduled']);
        $jobInstance->setRawParameters($normalizedJobInstance['raw_parameters']);

        return $jobInstance;
    }
}

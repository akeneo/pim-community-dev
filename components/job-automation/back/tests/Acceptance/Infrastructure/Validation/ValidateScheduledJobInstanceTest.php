<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\ScheduledJobInstance as ScheduledJobInstanceConstraint;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateScheduledJobInstanceTest extends AbstractValidationTest
{
    /**
     * @dataProvider validScheduledJobInstance
     */
    public function test_it_does_not_build_violations_when_scheduled_job_instance_is_valid(array $normalizedValue): void
    {
        $value = $this->createScheduledJobInstance($normalizedValue);
        $violations = $this->getValidator()->validate($value, new ScheduledJobInstanceConstraint());

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
        $value = $this->createScheduledJobInstance($normalizedValue);
        $violations = $this->getValidator()->validate($value, new ScheduledJobInstanceConstraint());

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
            'invalid job with scheduled disabled' => [
                [
                    'scheduled' => false,
                    'type' => 'export',
                    'raw_parameters' => [
                        'storage' => [
                            'type' => 'none'
                        ],
                    ],
                    'notified_users' => [],
                    'notified_user_groups' => [],
                ],
                'akeneo.job_automation.validation.scheduled_should_be_enabled',
                '',
            ],
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
                'akeneo.job_automation.validation.import_should_have_storage',
                '',
            ],
        ];
    }

    private function createScheduledJobInstance(array $normalizedScheduledJobInstance): ScheduledJobInstance {
        return new ScheduledJobInstance(
            'my_job',
            'my_job',
            $normalizedScheduledJobInstance['type'],
            $normalizedScheduledJobInstance['raw_parameters'],
            $normalizedScheduledJobInstance['notified_users'],
            $normalizedScheduledJobInstance['notified_user_groups'],
            $normalizedScheduledJobInstance['scheduled'],
            '* * * * *',
            new \DateTimeImmutable(),
            null,
            'job_automated_my_job',
        );
    }
}

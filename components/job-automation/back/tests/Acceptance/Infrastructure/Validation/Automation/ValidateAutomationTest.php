<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Automation;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Automation;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateAutomationTest extends AbstractValidationTest
{
    /**
     * @dataProvider validAutomation
     */
    public function test_it_does_not_build_violations_when_automation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Automation());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidAutomation
     */
    public function test_it_builds_violations_when_automation_is_invalid(
        array $value,
        string $expectedErrorMessage,
        string $expectedErrorPath,
    ): void {
        $violations = $this->getValidator()->validate($value, new Automation());

        $this->assertHasValidationError(
            $expectedErrorMessage,
            $expectedErrorPath,
            $violations,
        );
    }

    public function validAutomation(): array
    {
        return [
            'Valid enabled automation configuration' => [
                [
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid disabled automation configuration' => [
                [
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid empty running user groups' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid automation configuration with a setup_date' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid automation configuration with a last_execution_date' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => '2022-02-16T14:00:00+00:00',
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid automation configuration with a null last_execution_date' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => null,
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid empty notification user groups' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => ['IT Support'],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => null,
                    'notification_user_groups' => [],
                    'notification_users' => ['Julia'],
                ],
            ],
            'Valid empty notification users' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => ['IT Support'],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => null,
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => [],
                ],
            ],
        ];
    }

    public function invalidAutomation(): array
    {
        return [
            'Automation configuration with unknown key' => [
                [
                    'cron_expression' => '0 0 * * *',
                    'unknown_key' => ['IT Support'],
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
                'This field was not expected.',
                '[unknown_key]',
            ],
            'Automation configuration with invalid running user groups' => [
                [
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => 'IT Support',
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => ['Julia'],
                ],
                'This value should be of type array.',
                '[running_user_groups]',
            ],
            'Automation configuration with a null setup_date' => [
                [
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                    'setup_date' => null,
                    'last_execution_date' => null,
                ],
                'This value should not be blank.',
                '[setup_date]',
            ],
            'Automation configuration with invalid notification user groups' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => null,
                    'notification_user_groups' => 'Manager',
                    'notification_users' => ['Julia'],
                ],
                'This value should be of type array.',
                '[notification_user_groups]',
            ],
            'Automation configuration with invalid notification users' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                    'setup_date' => '2022-02-15T14:00:00+00:00',
                    'last_execution_date' => null,
                    'notification_user_groups' => ['Manager'],
                    'notification_users' => 'Julia',
                ],
                'This value should be of type array.',
                '[notification_users]',
            ],
        ];
    }
}

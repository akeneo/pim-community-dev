<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Automation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation\Automation;
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

    public function validAutomation(): array
    {
        return [
            'Valid enabled automation configuration' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                ],
            ],
            'Valid disabled automation configuration' => [
                [
                    'is_enabled' => false,
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => ['IT Support'],
                ],
            ],
            'Valid empty running user groups' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * 0',
                    'running_user_groups' => [],
                ],
            ],
        ];
    }

    public function invalidAutomation(): array
    {
        return [
            'Automation configuration with unknown key' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * *',
                    'unknown_key' => ['IT Support'],
                ],
                'This field was not expected.',
                '[unknown_key]',
            ],
            'Automation configuration with invalid running user groups' => [
                [
                    'is_enabled' => true,
                    'cron_expression' => '0 0 * * *',
                    'running_user_groups' => 'IT Support',
                ],
                'This value should be of type array.',
                '[running_user_groups]',
            ],
        ];
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
}

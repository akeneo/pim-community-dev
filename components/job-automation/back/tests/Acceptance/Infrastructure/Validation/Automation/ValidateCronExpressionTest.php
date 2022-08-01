<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Automation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation\CronExpression;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateCronExpressionTest extends AbstractValidationTest
{
    /**
     * @dataProvider validCronExpression
     */
    public function test_it_does_not_build_violations_when_cron_expression_is_valid(string $value): void
    {
        $violations = $this->getValidator()->validate($value, new CronExpression());

        $this->assertNoViolation($violations);
    }

    public function validCronExpression(): array
    {
        return [
            'Valid daily cron expression' => ['0 0 * * *'],
            'Valid weekly cron expression' => ['0 0 * * 0'],
            'Valid every 4 hours cron expression' => ['0 0/4 * * *'],
            'Valid every 8 hours cron expression' => ['0 0/8 * * *'],
            'Valid every 12 hours cron expression' => ['0 0/12 * * *'],
        ];
    }

    public function invalidCronExpression(): array
    {
        return [
            'Cron expression with invalid frequency option' => [
                '0 0 0 * *',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression cannot be weekly and hourly' => [
                '0 0/8 * * 1',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression with invalid hourly frequency' => [
                '0 0/1 * * *',
                CronExpression::INVALID_HOURLY_FREQUENCY,
                '',
            ],
            'Cron expression with invalid week day number value' => [
                '0 0 * * 7',
                CronExpression::INVALID_WEEK_DAY,
                '[week_day]',
            ],
            'Cron expression with invalid week day number type' => [
                '0 0 * * g',
                CronExpression::INVALID_WEEK_DAY,
                '[week_day]',
            ],
            'Cron expression with invalid minutes value' => [
                '76 0 * * *',
                CronExpression::INVALID_TIME,
                '[time]',
            ],
            'Cron expression with invalid minutes type' => [
                'h 0 * * *',
                CronExpression::INVALID_TIME,
                '[time]',
            ],
            'Cron expression with invalid hours value' => [
                '36 42 * * *',
                CronExpression::INVALID_TIME,
                '[time]',
            ],
            'Cron expression with invalid hours type' => [
                '36 w * * *',
                CronExpression::INVALID_TIME,
                '[time]',
            ],
        ];
    }

    /**
     * @dataProvider invalidCronExpression
     */
    public function test_it_builds_violations_when_cron_expression_is_invalid(
        string $value,
        string $expectedErrorMessage,
        string $expectedErrorPath,
    ): void {
        $violations = $this->getValidator()->validate($value, new CronExpression());

        $this->assertHasValidationError(
            $expectedErrorMessage,
            $expectedErrorPath,
            $violations,
        );
    }
}

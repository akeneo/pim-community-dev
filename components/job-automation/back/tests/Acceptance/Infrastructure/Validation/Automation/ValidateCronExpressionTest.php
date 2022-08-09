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
            'Valid daily cron expression at midnight' => ['0 0 * * *'],
            'Valid daily cron expression at 02:40' => ['40 2 * * *'],
            'Valid weekly cron expression at midnight' => ['0 0 * * 0'],
            'Valid weekly cron expression at 10:30' => ['30 10 * * 0'],
            'Valid every 4 hours cron expression' => ['0 0/4 * * *'],
            'Valid every 8 hours cron expression' => ['0 0/8 * * *'],
            'Valid every 12 hours cron expression' => ['0 0/12 * * *'],
        ];
    }

    public function invalidCronExpression(): array
    {
        return [
            'Cron expression with invalid type' => [
                4,
                'This value should be of type string.',
                '',
            ],
            'Cron expression with too much sub expressions' => [
                '0 0 0 * * *',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression with too few sub expressions' => [
                '0 0 0 *',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression with invalid frequency option' => [
                '0 0 0 * *',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression cannot be weekly and hourly' => [
                '0 0/12 * * 1',
                CronExpression::INVALID_FREQUENCY_OPTION,
                '',
            ],
            'Cron expression with too frequent hourly frequency' => [
                '0 0/1 * * *',
                CronExpression::INVALID_HOURLY_FREQUENCY,
                '',
            ],
            'Cron expression with hourly frequency with invalid time' => [
                '10 2/4 * * *',
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
            'Cron expression with unallowed minutes value' => [
                '16 0 * * *',
                CronExpression::INVALID_MINUTES,
                '[minutes]',
            ],
            'Cron expression with invalid minutes value' => [
                '80 0 * * *',
                CronExpression::INVALID_MINUTES,
                '[minutes]',
            ],
            'Cron expression with invalid minutes type' => [
                'h 0 * * *',
                CronExpression::INVALID_MINUTES,
                '[minutes]',
            ],
            'Cron expression with invalid hours value' => [
                '20 42 * * *',
                CronExpression::INVALID_HOURS,
                '[hours]',
            ],
            'Cron expression with invalid hours type' => [
                '20 w * * *',
                CronExpression::INVALID_HOURS,
                '[hours]',
            ],
        ];
    }

    /**
     * @dataProvider invalidCronExpression
     */
    public function test_it_builds_violations_when_cron_expression_is_invalid(
        mixed $value,
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

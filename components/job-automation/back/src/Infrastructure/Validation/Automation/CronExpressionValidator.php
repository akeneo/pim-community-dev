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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CronExpressionValidator extends ConstraintValidator
{
    private const VALID_HOURLY_EXPRESSIONS = ['0 */4 * * *', '0 */8 * * *', '0 */12 * * *'];
    private const VALID_MINUTES = [0, 10, 20, 30, 40, 50];
    private const MIN_HOUR = 0;
    private const MAX_HOUR = 23;
    private const MIN_WEEK_DAY_NUMBER = 0;
    private const MAX_WEEK_DAY_NUMBER = 6;

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CronExpression) {
            throw new UnexpectedTypeException($constraint, CronExpression::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Type('string'));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $subExpressions = explode(' ', $value);

        if (5 !== count($subExpressions)) {
            $this->context->buildViolation(CronExpression::INVALID_FREQUENCY_OPTION)
                ->addViolation();

            return;
        }

        [$minutes, $hours, $day, $month, $weekDayNumber] = $subExpressions;

        if ('*' !== $day || '*' !== $month) {
            $this->context->buildViolation(CronExpression::INVALID_FREQUENCY_OPTION)
                ->addViolation();
        }

        $isWeekly = '*' !== $weekDayNumber;
        $isHourly = false !== strpos($hours, '/');

        if ($isWeekly && $isHourly) {
            $this->context->buildViolation(CronExpression::INVALID_FREQUENCY_OPTION)
                ->addViolation();
        }

        if ($isHourly) {
            $this->validateHourlyExpression($value);
        } else {
            $this->validateTime($hours, $minutes);
        }

        if ($isWeekly) {
            $this->validateWeekDayNumber($weekDayNumber);
        }
    }

    private function validateHourlyExpression(string $cronExpression): void
    {
        if (!in_array($cronExpression, self::VALID_HOURLY_EXPRESSIONS)) {
            $this->context->buildViolation(CronExpression::INVALID_HOURLY_FREQUENCY)
                ->addViolation();
        }
    }

    private function validateWeekDayNumber(string $weekDayNumber): void
    {
        $isWeekDayValid = is_numeric($weekDayNumber)
            && self::MIN_WEEK_DAY_NUMBER <= (int) $weekDayNumber
            && self::MAX_WEEK_DAY_NUMBER >= (int) $weekDayNumber;

        if (!$isWeekDayValid) {
            $this->context->buildViolation(CronExpression::INVALID_WEEK_DAY)
                ->atPath('[week_day]')
                ->addViolation();
        }
    }

    private function validateTime(string $hours, string $minutes): void
    {
        $isHoursValid = is_numeric($hours)
            && self::MIN_HOUR <= (int) $hours
            && self::MAX_HOUR >= (int) $hours;

        if (!$isHoursValid) {
            $this->context->buildViolation(CronExpression::INVALID_HOURS)
                ->atPath('[hours]')
                ->addViolation();
        }

        $isMinutesValid = is_numeric($minutes) && in_array((int) $minutes, self::VALID_MINUTES);

        if (!$isMinutesValid) {
            $this->context->buildViolation(CronExpression::INVALID_MINUTES)
                ->atPath('[minutes]')
                ->addViolation();
        }
    }
}

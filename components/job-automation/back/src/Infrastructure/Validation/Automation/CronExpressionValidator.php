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

class CronExpressionValidator extends ConstraintValidator
{
    private const VALID_HOURLY_FREQUENCIES = [4, 8, 12];

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CronExpression) {
            throw new UnexpectedTypeException($constraint, CronExpression::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Type('string'));

        [$minutes, $hours, $day, $month, $weekDayNumber] = explode(' ', $value);

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
            [$hours, $hoursStep] = explode('/', $hours);

            if (!in_array((int) $hoursStep, self::VALID_HOURLY_FREQUENCIES)) {
                $this->context->buildViolation(CronExpression::INVALID_HOURLY_FREQUENCY)
                    ->addViolation();
            }
        }

        if ($isWeekly) {
            $this->validateWeekDayNumber($weekDayNumber);
        }

        $this->validateTime($hours, $minutes);
    }

    private function validateWeekDayNumber(string $weekDayNumber): void
    {
        $isWeekDayValid = is_numeric($weekDayNumber) && 0 <= (int) $weekDayNumber && 6 >= (int) $weekDayNumber;

        if (!$isWeekDayValid) {
            $this->context->buildViolation(CronExpression::INVALID_WEEK_DAY)
                ->atPath('[week_day]')
                ->addViolation();
        }
    }

    private function validateTime(string $hours, string $minutes): void
    {
        $isHoursValid = is_numeric($hours) && (0 <= (int) $hours && 23 >= (int) $hours);
        $isMinutesValid = is_numeric($minutes) && (0 <= (int) $minutes && 59 >= (int) $minutes);

        if (!$isHoursValid || !$isMinutesValid) {
            $this->context->buildViolation(CronExpression::INVALID_TIME)
                ->atPath('[time]')
                ->addViolation();
        }
    }
}

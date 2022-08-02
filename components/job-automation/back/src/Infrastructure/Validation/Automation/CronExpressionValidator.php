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
    private const MAX_HOURLY_FREQUENCY = 24 / 4;

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
        $isHourly = false !== strpos($hours, ',');

        if ($isWeekly && $isHourly) {
            $this->context->buildViolation(CronExpression::INVALID_FREQUENCY_OPTION)
                ->addViolation();
        }

        if ($isHourly) {
            $this->validateHourlyFrequency($hours);
        }

        if ($isWeekly) {
            $this->validateWeekDayNumber($weekDayNumber);
        }

        $this->validateTime($hours, $minutes);
    }

    private function validateHourlyFrequency(string $hours): void
    {
        $hourlySteps = explode(',', $hours);

        if (count($hourlySteps) > self::MAX_HOURLY_FREQUENCY) {
            $this->context->buildViolation(CronExpression::INVALID_HOURLY_FREQUENCY)
                ->addViolation();
        }
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
        $isMinutesValid = is_numeric($minutes) && (0 <= (int) $minutes && 59 >= (int) $minutes);
        $areHourlyStepsValid = array_reduce(
            explode(',', $hours),
            static fn (bool $isValid, string $hourlyStep) => $isValid
                && is_numeric($hourlyStep)
                && 0 <= (int) $hourlyStep
                && 23 >= (int) $hourlyStep,
            true,
        );

        if (!$areHourlyStepsValid || !$isMinutesValid) {
            $this->context->buildViolation(CronExpression::INVALID_TIME)
                ->atPath('[time]')
                ->addViolation();
        }
    }
}

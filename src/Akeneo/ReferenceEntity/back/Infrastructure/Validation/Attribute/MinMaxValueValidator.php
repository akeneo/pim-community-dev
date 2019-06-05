<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMinMaxValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class MinMaxValueValidator extends ConstraintValidator
{
    public function validate($updateMinMaxValueCommand, Constraint $constraint)
    {
        $minHasValue = $this->hasValue($updateMinMaxValueCommand->minValue);
        $isMinValid = false;
        if ($minHasValue) {
            $isMinValid = $this->checkMin($updateMinMaxValueCommand->minValue);
        }

        $maxHasValue = $this->hasValue($updateMinMaxValueCommand->maxValue);
        $isMaxValid = false;
        if ($maxHasValue) {
            $isMaxValid = $this->checkMax($updateMinMaxValueCommand->maxValue);
        }

        if ($minHasValue && $isMinValid && $maxHasValue && $isMaxValid) {
            $this->checkMinAndMaxAreCompatible($updateMinMaxValueCommand->minValue, $updateMinMaxValueCommand->maxValue);
        }
    }

    private function hasValue(?string $value): bool
    {
        return null !== $value;
    }

    private function checkMin($minValue): bool
    {
        $isMinValid = is_numeric($minValue);
        if (!$isMinValid) {
            $this->buildViolation('minValue');
        }
        return $isMinValid;
    }

    private function checkMax($maxValue): bool
    {
        $isMaxValid = is_numeric($maxValue);
        if (!$isMaxValid) {
            $this->buildViolation('maxValue');
        }
        return $isMaxValid;
    }

    private function buildViolation(string $path): void
    {
        $this->context->buildViolation(MinMaxValue::MESSAGE_SHOULD_BE_A_NUMBER)
            ->atPath($path)
            ->addViolation();
    }

    private function checkMinAndMaxAreCompatible(?string $minValue, ?string $maxValue): void
    {
        if ($this->isMinGreaterThanMax($minValue, $maxValue)) {
            $this->context->buildViolation(MinMaxValue::MESSAGE_MIN_CANNOT_BE_GREATER_THAN_MAX)
                ->atPath('minValue')
                ->addViolation();
        }
    }

    private function isMinGreaterThanMax(string $minValue, string $maxValue): bool
    {
        $min = AttributeLimit::fromString($minValue);
        $max = AttributeLimit::fromString($maxValue);

        return $min->isGreater($max);
    }
}

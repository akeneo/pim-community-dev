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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class MinMaxValueValidator extends ConstraintValidator
{
    public function validate($updateMinMaxValueCommand, Constraint $constraint)
    {
        $this->checkLimit($updateMinMaxValueCommand->minValue, 'min_value');
        $this->checkLimit($updateMinMaxValueCommand->maxValue, 'max_value');

        if (0 === $this->context->getViolations()->count()) {
            $this->checkMinAndMaxAreCompatible($updateMinMaxValueCommand->minValue, $updateMinMaxValueCommand->maxValue);
        }
    }

    private function checkLimit(?string $value, string $propertyPath): void
    {
        if ($this->isLimitBeingRemoved($value)) {
            return;
        }

        $this->checkValueType($value, $propertyPath);
    }

    private function checkMinAndMaxAreCompatible(?string $minValue, ?string $maxValue): void
    {
        if ($this->isLimitBeingRemoved($minValue) || $this->isLimitBeingRemoved($maxValue)) {
            return;
        }

        if ($this->isMinIsGreaterThanMax($minValue, $maxValue)) {
            $this->context->buildViolation(MinMaxValue::MESSAGE_MIN_CANNOT_BE_GREATER_THAN_MAX)
                ->atPath('minValue')
                ->addViolation();
        }
    }

    private function checkValueType(?string $limitValue, string $propertyPath): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $limitValue,
            [
                new Constraints\Callback(
                    function ($value, ExecutionContextInterface $context) {
                        if (null !== $value && !is_numeric($value)) {
                            $context->buildViolation(MinMaxValue::MESSAGE_SHOULD_BE_A_NUMBER)->addViolation();
                        }
                    }
                )
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )->atPath($propertyPath)->addViolation();
            }
        }
    }

    private function isLimitBeingRemoved(?string $minValue): bool
    {
        return null === $minValue;
    }

    private function isMinIsGreaterThanMax(string $minValue, string $maxValue): bool
    {
        $min = AttributeLimit::fromString($minValue);
        $max = AttributeLimit::fromString($maxValue);

        return $min->isGreater($max);
    }
}

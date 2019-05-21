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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
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
        $this->checkLimit($updateMinMaxValueCommand->minValue);
        $this->checkLimit($updateMinMaxValueCommand->maxValue);
        $this->checkMinAndMaxAreCompatible($updateMinMaxValueCommand->minValue, $updateMinMaxValueCommand->maxValue);
    }

    private function checkLimit(?string $minValue): void
    {
        if ($this->isLimitBeingRemoved($minValue)) {
            return;
        }
        $this->checkMinValue($minValue);
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

    private function checkMinValue(?string $updateMinValueCommand): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $updateMinValueCommand,
            [
                new Constraints\Callback(
                    function ($minValue, ExecutionContextInterface $context, $payload) {
                        if (null !== $minValue && !is_numeric($minValue)) {
                            $context->buildViolation(MinMaxValue::MESSAGE_SHOULD_BE_A_NUMBER)->addViolation();
                        }
                    }
                )
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
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

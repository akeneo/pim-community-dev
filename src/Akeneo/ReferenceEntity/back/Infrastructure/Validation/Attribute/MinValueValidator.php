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

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMinValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
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
class MinValueValidator extends ConstraintValidator
{
    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($updateMinValueCommand, Constraint $constraint)
    {
        if ($this->isMinValueBeingUnset($updateMinValueCommand)) {
            return;
        }
        $this->checkMinValue($updateMinValueCommand);
    }

    private function checkMinValue($updateMinValueCommand): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $updateMinValueCommand,
            [
                new Constraints\Callback(
                    function ($command, ExecutionContextInterface $context, $payload)
                    {
                        if (null !== $command->minValue && !is_numeric($command->minValue)) {
                            $context->buildViolation(MinValue::MESSAGE_SHOULD_BE_A_NUMBER)->addViolation();
                        }
                    }
                ),
                new Constraints\Callback(
                    function ($command, ExecutionContextInterface $context, $payload)
                    {
                        $currentMaxValue = $this->maxValue($command);
                        if (null !== $currentMaxValue && $this->isMinIsGreaterThanMax(
                                $command->minValue,
                                $currentMaxValue
                            )) {
                            $context->buildViolation(MinValue::MESSAGE_MIN_CANNOT_BE_GREATER_THAN_MAX)->addViolation();
                        }
                    }
                ),
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

    private function isMinValueBeingUnset(EditMinValueCommand $minValue): bool
    {
        return null === $minValue->minValue;
    }

    private function maxValue(EditMinValueCommand $command): ?string
    {
        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString($command->identifier)
        );

        return $attribute->normalize()['max_value'];
    }

    private function isMinIsGreaterThanMax(string $minValue, string $currentMaxValue): bool
    {
        return (float) $minValue > (float) $currentMaxValue;
    }
}

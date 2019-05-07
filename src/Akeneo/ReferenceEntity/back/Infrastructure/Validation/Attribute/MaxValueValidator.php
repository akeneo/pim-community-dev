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

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxValueCommand;
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
class MaxValueValidator extends ConstraintValidator
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

    public function validate($updateMaxValueCommand, Constraint $constraint)
    {
        if ($this->isMaxValueBeingUnset($updateMaxValueCommand)) {
            return;
        }
        $this->checkMaxValue($updateMaxValueCommand);
    }

    private function checkMaxValue(EditMaxValueCommand $updateMaxValueCommand): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $updateMaxValueCommand,
            [
                new Constraints\Callback(
                    function ($command, ExecutionContextInterface $context, $payload)
                    {
                        if (null !== $command->maxValue && !is_numeric($command->maxValue)) {
                            $context->buildViolation(MaxValue::MESSAGE_SHOULD_BE_A_NUMBER)->addViolation();
                        }
                    }
                ),
                new Constraints\Callback(
                    function ($command, ExecutionContextInterface $context, $payload)
                    {
                        $currentMinValue = $this->minValue($command);
                        if (null !== $currentMinValue && $this->isMaxLowerThanMin(
                                $command->maxValue,
                                $currentMinValue
                            )) {
                            $context->buildViolation(MaxValue::MESSAGE_MAX_CANNOT_BE_LOWER_THAN_MIN)->addViolation();
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

    private function isMaxValueBeingUnset(EditMaxValueCommand $maxValue): bool
    {
        return null === $maxValue->maxValue;
    }

    private function minValue(EditMaxValueCommand $command): ?string
    {
        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString($command->identifier)
        );

        return $attribute->normalize()['min_value'];
    }

    private function isMaxLowerThanMin(string $maxValue, string $currentMinValue): bool
    {
        return (float) $maxValue < (float) $currentMinValue;
    }
}

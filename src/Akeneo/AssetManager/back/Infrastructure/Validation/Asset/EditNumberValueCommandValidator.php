<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommand;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditNumberValueCommand as EditNumberValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class EditNumberValueCommandValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof EditNumberValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditNumberValueCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditNumberValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditNumberValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditNumberValueCommand $command): void
    {
        $violations = $this->checkType($command);
        if (0 === $violations->count()) {
            $violations->addAll($this->checkNumericValue($command));
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath((string)$command->attribute->getCode())
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->addViolation();
            }
        }
    }

    private function checkType(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $command->number,
            [
                new Constraints\Type(
                    [
                        'type'    => 'string',
                        'message' => EditNumberValueCommandConstraint::NUMBER_SHOULD_BE_STRING,
                    ]
                ),
            ]
        );

        return $violations;
    }

    private function checkNumericValue(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        if ($command->attribute->allowsDecimalValues()) {
            $violations = $this->checkIsFloat($command);
        } else {
            $violations = $this->checkIsInteger($command);
        }

        if ($violations->count() > 0) {
            return $violations;
        }

        $violations = $this->checkMinBoundary($command);
        $violations->addAll($this->checkMaxBoundary($command));

        return $violations;
    }

    private function checkMinBoundary(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if (!$command->attribute->isMinLimitless()) {
            $validator = Validation::createValidator();
            $violations = $validator->validate(
                $command->number,
                [
                    new Constraints\Range(['min' => $command->attribute->minValue()])
                ]
            );
        }

        return $violations;
    }

    private function checkMaxBoundary(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if (!$command->attribute->isMaxLimitless()) {
            $validator = Validation::createValidator();
            $violations = $validator->validate(
                $command->number,
                [
                    new Constraints\Range(['max' => $command->attribute->maxValue()])
                ]
            );
        }

        return $violations;
    }

    private function checkIsFloat(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $violationList = new ConstraintViolationList();

        if (!is_numeric($command->number)) {
            $violation = new ConstraintViolation(
                EditNumberValueCommandConstraint::NUMBER_SHOULD_BE_NUMERIC,
                null,
                [],
                [],
                '',
                '',
                0,
                0,
                null,
                $command->number
            );

            $violationList->add($violation);
        }

        return $violationList;
    }

    private function checkIsInteger(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $this->isInteger($command),
            new Constraints\IsTrue(['message' => EditNumberValueCommandConstraint::NUMBER_SHOULD_BE_INTEGER])
        );

        if (0 === $violations->count()) {
            $violations->addAll(
                $validator->validate(
                    $this->isTooLong($command),
                    new Constraints\IsFalse(['message' => EditNumberValueCommandConstraint::INTEGER_TOO_LONG])
                )
            );
        }

        return $violations;
    }

    private function isInteger(EditNumberValueCommand $command): bool
    {
        return 1 === preg_match('/^-?[0-9]+$/', $command->number);
    }

    /**
     * When integers are passed in as strings, they might be greater than the integer's limit (imposed by the system)
     * We need to check this in order to prevent loosing precisions.
     *
     * @param EditNumberValueCommand $command
     *
     * @return bool
     */
    private function isTooLong(EditNumberValueCommand $command): bool
    {
        return ((string) ((int) $command->number)) !== (string) $command->number;
    }
}

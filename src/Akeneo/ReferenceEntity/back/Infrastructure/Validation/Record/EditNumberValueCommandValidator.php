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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditNumberValueCommand;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditNumberValueCommand as EditNumberValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
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
                    'Expected argument to be of class "%s", "%s" given', EditNumberValueCommand::class,
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
                    ->atPath((string) $command->attribute->getCode())
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
        $violations = $validator->validate($command->number, [
            new Constraints\Type([
                'type' => 'string',
                'message' => EditNumberValueCommandConstraint::NUMBER_SHOULD_BE_STRING,
            ]),
        ]);

        return $violations;
    }

    private function checkNumericValue(EditNumberValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        if (!$command->attribute->allowsDecimalValues()) {
            $violations = $validator->validate($command->number, [
                new Constraints\Type([
                    'type' => 'digit',
                    'message' => EditNumberValueCommandConstraint::NUMBER_SHOULD_NOT_BE_DECIMAL,
                ]),
            ]);

            return $violations;
        }

        $violations = $validator->validate($command->number, [
            new Constraints\Type([
                'type' => 'numeric',
                'message' => EditNumberValueCommandConstraint::NUMBER_SHOULD_BE_NUMERIC,
            ]),
        ]);

        return $violations;
    }
}

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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditOptionCollectionValueCommand as EditOptionCollectionValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditOptionCollectionValueCommandValidator extends ConstraintValidator
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
        if (!$command instanceof EditOptionCollectionValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditOptionCollectionValueCommand::class,
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
        if (!$constraint instanceof EditOptionCollectionValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditOptionCollectionValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditOptionCollectionValueCommand $command): void
    {
        if ($this->validType($command)) {
            $this->checkOptionExists($command);
        }
    }

    private function validType(EditOptionCollectionValueCommand $command): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->optionCodes, new Constraints\All([
                'constraints' => [
                    new Constraints\Type('string'),
                ],
            ]
        ));

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

            return false;
        }

        return true;
    }

    private function checkOptionExists(EditOptionCollectionValueCommand $command): void
    {
        $existingOptionCodes = array_map(function (AttributeOption $attributeOption) {
            return (string) $attributeOption->getCode();
        }, $command->attribute->getAttributeOptions());

        $unexistingOptionCodes = array_diff($command->optionCodes, $existingOptionCodes);

        if (!empty($unexistingOptionCodes)) {
            $this->context
                ->buildViolation(EditOptionCollectionValueCommandConstraint::OPTION_DOES_NOT_EXIST)
                ->setParameter('%option_codes%', implode(', ', $unexistingOptionCodes))
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();
        }
    }
}

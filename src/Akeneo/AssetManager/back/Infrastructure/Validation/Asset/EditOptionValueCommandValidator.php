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

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionValueCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditOptionValueCommand as EditOptionValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditOptionValueCommandValidator extends ConstraintValidator
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
        if (!$command instanceof EditOptionValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditOptionValueCommand::class,
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
        if (!$constraint instanceof EditOptionValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditOptionValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditOptionValueCommand $command): void
    {
        if ($this->validType($command)) {
            $this->checkOptionExists($command);
        }
    }

    private function validType(EditOptionValueCommand $command): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->optionCode, new Type('string'));

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

    private function checkOptionExists(EditOptionValueCommand $command): void
    {
        $existingOptionCodes = array_map(fn (AttributeOption $attributeOption) => (string) $attributeOption->getCode(), $command->attribute->getAttributeOptions());

        if (!in_array($command->optionCode, $existingOptionCodes)) {
            $this->context
                ->buildViolation(EditOptionValueCommandConstraint::OPTION_DOES_NOT_EXIST)
                ->setParameter('%option_code%', $command->optionCode)
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();
        }
    }
}

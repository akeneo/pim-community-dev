<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditCommandsValidator extends ConstraintValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($editCommand, Constraint $constraint)
    {
        if (!$constraint instanceof EditCommands) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
        if (!$editCommand instanceof EditAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                EditAttributeCommand::class, get_class($editCommand)));
        }

        if (empty($editCommand->editCommands)) {
            $this->context->addViolation('There should be updates to perform on the attribute. None found.');
        }

        foreach ($editCommand->editCommands as $command) {
            $violations = $this->validator->validate($command);
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath($violation->getPropertyPath())
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->addViolation();
            }
        }
    }
}

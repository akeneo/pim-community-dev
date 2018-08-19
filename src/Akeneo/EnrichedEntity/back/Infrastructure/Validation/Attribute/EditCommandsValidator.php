<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditCommandsValidator extends ConstraintValidator
{
    public function validate($editCommands, Constraint $constraint)
    {
        if (!$constraint instanceof EditCommands) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($editCommands, new Assert\NotBlank());
        if ($violations->count() > 0) {
            $this->addViolations($violations);

            return;
        }

        $this->validateEditCommands($editCommands);
    }

    private function validateEditCommands(array $editCommands): void
    {
        foreach ($editCommands as $editCommand) {
            $violations = $this->context->getValidator()->validate($editCommand);
            if ($violations->count() > 0) {
                $this->addViolations($violations);
            }
        }
    }

    private function addViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->context->addViolation(
                $violation->getMessage(),
                $violation->getParameters()
            );
        }
    }
}

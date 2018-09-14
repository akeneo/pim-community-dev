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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Infrastructure\Validation\Record\EditTextValueCommand as EditTextValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditTextValueCommandValidator extends ConstraintValidator
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
        if (!$command instanceof EditTextValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditTextValueCommand::class, get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditTextValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditTextValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditTextValueCommand $command): void
    {
        /** @var TextAttribute $attribute */
        $attribute = $command->attribute;
        $validator = Validation::createValidator();

        if (!$command->attribute instanceof TextAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected command attribute to be of class "%s", "%s" given', TextAttribute::class, get_class($command)
                )
            );
        }

        if (null === $command->channel && $attribute->hasValuePerChannel()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A channel is expected for attribute "%s" because it has a value per channel', $attribute->getCode()
                )
            );
        }

        if (null === $command->locale && $attribute->hasValuePerLocale()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A locale is expected for attribute "%s" because it has a value per locale', $attribute->getCode()
                )
            );
        }

        $violations = $validator->validate($command->data, [
            new Constraints\Length([
                'min' => 0,
                'max' => $attribute->getMaxLength()->normalize(),
            ])
        ]);

        if ($attribute->isValidationRuleSetToRegularExpression()) {
            $violations->addAll($validator->validate($command->data, [
                new Constraints\Regex([
                    'pattern' => $attribute->getRegularExpression()->normalize(),
                ]),
            ]));
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}

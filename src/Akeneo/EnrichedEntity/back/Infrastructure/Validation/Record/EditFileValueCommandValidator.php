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

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Infrastructure\Validation\Record\EditFileValueCommand as EditFileValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditFileValueCommandValidator extends ConstraintValidator
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
        if (!$command instanceof EditFileValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditFileValueCommand::class, get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditFileValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditFileValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditFileValueCommand $command): void
    {
        /** @var ImageAttribute $attribute */
        $attribute = $command->attribute;
        $validator = Validation::createValidator();

        if (!$command->attribute instanceof ImageAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected command attribute to be of class "%s", "%s" given', ImageAttribute::class, get_class($command)
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

        $violations = $validator->validate($command->data, new Constraints\Collection([
            // TODO: Our validation is only based on the file extension... maybe we should check for mimetypes.
            'originalFilename' => new Constraints\Regex([
                'pattern' => sprintf('/^.*\.(%s)$/', implode('|', $attribute->getAllowedExtensions()->normalize()))
            ]),
        ]));


        // TODO: Note, I don't check file size, because here the file would have already be moved from tmp dir to media dir.
        // This check on size should be done prior to command validation imho

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

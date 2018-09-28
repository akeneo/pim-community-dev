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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
                    'Expected argument to be of class "%s", "%s" given', EditFileValueCommand::class,
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
                    'Expected command attribute to be of class "%s", "%s" given', ImageAttribute::class,
                    get_class($command)
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

        $violations = $this->checkPropertyTypes($command);
        if (0 === $violations->count()) {
            if (!$attribute->getAllowedExtensions()->isAllAllowed()) {
                $violations = $validator->validate(
                    $command->originalFilename,
                    [
                        new Constraints\Callback(function ($originalFilename, ExecutionContextInterface $context, $payload) use (
                            $attribute
                        ) {
                            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
                            $regularExpression = sprintf(
                                '/^.*\.(%s)$/',
                                implode('|', $attribute->getAllowedExtensions()->normalize())
                            );
                            if (!preg_match($regularExpression, $originalFilename, $matches)) {
                                $this->context
                                    ->buildViolation(EditFileValueCommandConstraint::FILE_EXTENSION_NOT_ALLOWED_MESSAGE)
                                    ->setParameter('%file_extension%', '.' . $extension)
                                    ->setParameter('%allowed_file_extensions%',
                                        implode(',', $attribute->getAllowedExtensions()->normalize()))
                                    ->atPath((string) $attribute->getCode())
                                    ->addViolation();
                            }
                        }),
                    ]
                );
            }
            if ($attribute->hasMaxFileSizeLimit()) {
                $violations->addAll($validator->validate($command->filePath, [
                        new Constraints\File([
                                'maxSize' => $this->getKbValue($command),
                                'maxSizeMessage' => EditFileValueCommandConstraint::FILE_SIZE_EXCEEDED_MESSAGE
                            ]
                        ),
                    ]
                ));
            }
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

    private function checkPropertyTypes($command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->originalFilename, [new Constraints\Type('string')]);
        $violations->addAll($validator->validate($command->filePath, [new Constraints\Type('string')]));

        return $violations;
    }

    private function getKbValue(EditFileValueCommand $command): string
    {
        $kb = $command->attribute->getMaxFileSize()->floatValue() * 1000;

        return (int) $kb . 'k';
    }
}

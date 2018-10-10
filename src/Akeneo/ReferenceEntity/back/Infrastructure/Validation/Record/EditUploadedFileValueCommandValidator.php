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

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditUploadedFileValueCommand as EditUploadedFileValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditUploadedFileValueCommandValidator extends ConstraintValidator
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
        if (!$command instanceof EditUploadedFileValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditUploadedFileValueCommand::class,
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
        if (!$constraint instanceof EditUploadedFileValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditUploadedFileValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditUploadedFileValueCommand $command): void
    {
        /** @var ImageAttribute $attribute */
        $attribute = $command->attribute;

        if (!$command->attribute instanceof ImageAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected command attribute to be of class "%s", "%s" given', ImageAttribute::class,
                    get_class($command)
                )
            );
        }

        $violations = $this->checkPropertyTypes($command);
        if (0 === $violations->count()) {
            $violations = $this->checkFile($command, $attribute);
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

    private function checkPropertyTypes(EditUploadedFileValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->originalFilename, [new Constraints\Type('string')]);
        $violations->addAll($validator->validate($command->filePath, [new Constraints\Type('string')]));

        return $violations;
    }

    private function checkFile(EditUploadedFileValueCommand $command, ImageAttribute $attribute): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();

        if (!$attribute->getAllowedExtensions()->isAllAllowed() && null !== $command->originalFilename) {
            $violations->addAll($validator->validate(
                $command->originalFilename,
                [
                    new Constraints\Callback(function ($originalFilename) use ($attribute) {
                        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
                        $regularExpression = sprintf(
                            '/^.*\.(%s)$/',
                            implode('|', $attribute->getAllowedExtensions()->normalize())
                        );
                        if (!preg_match($regularExpression, $originalFilename, $matches)) {
                            $this->context
                                ->buildViolation(EditUploadedFileValueCommandConstraint::FILE_EXTENSION_NOT_ALLOWED_MESSAGE)
                                ->setParameter('%file_extension%', '.' . $extension)
                                ->setParameter('%allowed_file_extensions%',
                                    implode(',', $attribute->getAllowedExtensions()->normalize()))
                                ->atPath((string) $attribute->getCode())
                                ->addViolation();
                        }
                    }),
                ]
            ));
        }

        if ($attribute->hasMaxFileSizeLimit()) {
            $violations->addAll($validator->validate($command->filePath, [
                    new Constraints\File([
                        'maxSize' => $this->getMaxFileSizeInKb($attribute),
                        'maxSizeMessage' => EditUploadedFileValueCommandConstraint::FILE_SIZE_EXCEEDED_MESSAGE
                    ]),
                ]
            ));
        }

        return $violations;
    }

    private function getMaxFileSizeInKb(ImageAttribute $attribute): string
    {
        $kb = $attribute->getMaxFileSize()->floatValue() * 1000;

        return (int) $kb . 'k';
    }
}

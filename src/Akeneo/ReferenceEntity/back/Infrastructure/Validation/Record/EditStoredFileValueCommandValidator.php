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

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Query\File\FileExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditStoredFileValueCommand as EditStoredFileValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * NOTE: This validator trusts the command values, this could be bypassed by the front intent
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditStoredFileValueCommandValidator extends ConstraintValidator
{
    public function __construct(
        private FileExistsInterface $fileExists
    ) {
    }

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
        if (!$command instanceof EditStoredFileValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditStoredFileValueCommand::class,
                    $command::class
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditStoredFileValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditStoredFileValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditStoredFileValueCommand $command): void
    {
        /** @var ImageAttribute $attribute */
        $attribute = $command->attribute;

        if (!$command->attribute instanceof ImageAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected command attribute to be of class "%s", "%s" given',
                    ImageAttribute::class,
                    $command::class
                )
            );
        }

        if (is_string($command->filePath) && '' !== $command->filePath && !$this->fileExists->exists($command->filePath)) {
            $this->context->buildViolation(EditStoredFileValueCommandConstraint::FILE_SHOULD_EXIST)
                ->atPath((string) $attribute->getCode())
                ->setParameter('%file_path%', $command->filePath)
                ->addViolation();
            return;
        }

        $violations = $this->checkPropertyTypes($command);
        if (0 === $violations->count()) {
            $violations = $this->checkFile($command, $attribute);
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $violationBuilder = $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath((string) $command->attribute->getCode())
                    ->setCode($violation->getCode())
                    ->setInvalidValue($violation->getInvalidValue());
                if ($violation->getPlural()) {
                    $violationBuilder->setPlural((int)$violation->getPlural());
                }
                $violationBuilder->addViolation();
            }
        }
    }

    private function checkPropertyTypes(EditStoredFileValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->originalFilename, [new Type('string')]);
        $violations->addAll($validator->validate($command->filePath, [new Type('string')]));
        $violations->addAll($validator->validate($command->size, [new Type('int')]));
        $violations->addAll($validator->validate($command->mimeType, [new Type('string')]));
        $violations->addAll($validator->validate($command->extension, [new Type('string')]));

        return $violations;
    }

    private function checkFile(EditStoredFileValueCommand $command, ImageAttribute $attribute): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();

        if (!$attribute->getAllowedExtensions()->isAllAllowed() && null !== $command->originalFilename) {
            $violations->addAll($validator->validate(
                $command->extension,
                [
                    new Callback(function ($extension) use ($attribute) {
                        if (!in_array($extension, $attribute->getAllowedExtensions()->normalize())) {
                            $this->context
                                ->buildViolation(EditStoredFileValueCommandConstraint::FILE_EXTENSION_NOT_ALLOWED_MESSAGE)
                                ->setParameter('%file_extension%', '.' . $extension)
                                ->setParameter(
                                    '%allowed_file_extensions%',
                                    implode(',', $attribute->getAllowedExtensions()->normalize())
                                )
                                ->atPath((string) $attribute->getCode())
                                ->addViolation();
                        }
                    }),
                ]
            ));
        }

        if ($attribute->hasMaxFileSizeLimit() && $command->size > $this->getMaxFileSizeInByte($attribute)) {
            $this->context
                ->buildViolation(EditStoredFileValueCommandConstraint::FILE_SIZE_EXCEEDED_MESSAGE)
                ->atPath((string) $attribute->getCode())
                ->addViolation();
        }

        return $violations;
    }

    private function getMaxFileSizeInByte(ImageAttribute $attribute): float
    {
        return $attribute->getMaxFileSize()->floatValue() * 1_000_000;
    }
}

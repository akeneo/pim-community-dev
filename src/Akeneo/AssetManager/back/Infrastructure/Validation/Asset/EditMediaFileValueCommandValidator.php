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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\File\FileExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditMediaFileValueCommand as EditMediaFileValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
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
class EditMediaFileValueCommandValidator extends ConstraintValidator
{
    /** @var FileExistsInterface */
    private $fileExists;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    public function __construct(
        FileExistsInterface $fileExists,
        AssetFamilyRepositoryInterface $assetFamilyRepository
    ) {
        $this->fileExists = $fileExists;
        $this->assetFamilyRepository = $assetFamilyRepository;
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
        if (!$command instanceof EditMediaFileValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditMediaFileValueCommand::class,
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
        if (!$constraint instanceof EditMediaFileValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditMediaFileValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditMediaFileValueCommand $command): void
    {
        /** @var MediaFileAttribute $attribute */
        $attribute = $command->attribute;

        if (!$command->attribute instanceof MediaFileAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected command attribute to be of class "%s", "%s" given', MediaFileAttribute::class,
                    get_class($command)
                )
            );
        }

        if ($this->attributeIsTargetOfATransformation($command)) {
            $this->context->buildViolation(EditMediaFileValueCommandConstraint::TARGET_READONLY)
                ->atPath((string) $attribute->getCode())
                ->addViolation();
            return;
        }

        if (is_string($command->filePath) && '' !== $command->filePath && !$this->fileExists->exists($command->filePath)) {
            $this->context->buildViolation(EditMediaFileValueCommandConstraint::FILE_SHOULD_EXIST)
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

    private function attributeIsTargetOfATransformation(EditMediaFileValueCommand $command): bool
    {
        $commandLocaleReference = $command->locale !== null ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();
        $commandChannelReference = $command->channel !== null ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();

        $transformations = $this->assetFamilyRepository
            ->getByIdentifier($command->attribute->getAssetFamilyIdentifier())
            ->getTransformationCollection();

        foreach ($transformations as $transformation) {
            /** @var $transformation Transformation */
            $target = $transformation->getTarget();

            if ($target->getAttributeCode()->equals($command->attribute->getCode()) &&
                $target->getLocaleReference()->equals($commandLocaleReference) &&
                $target->getChannelReference()->equals($commandChannelReference)
            ) {
                return true;
            }
        }

        return false;
    }

    private function checkPropertyTypes(EditMediaFileValueCommand $command): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($command->originalFilename, [new Constraints\Type('string')]);
        $violations->addAll($validator->validate($command->filePath, [new Constraints\Type('string')]));
        $violations->addAll($validator->validate($command->size, [new Constraints\Type('int')]));
        $violations->addAll($validator->validate($command->mimeType, [new Constraints\Type('string')]));
        $violations->addAll($validator->validate($command->extension, [new Constraints\Type('string')]));

        return $violations;
    }

    private function checkFile(EditMediaFileValueCommand $command, MediaFileAttribute $attribute): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();

        if (!$attribute->getAllowedExtensions()->isAllAllowed() && null !== $command->originalFilename) {
            $violations->addAll($validator->validate(
                $command->extension,
                [
                    new Constraints\Callback(function ($extension) use ($attribute) {
                        if (!in_array(strtolower($extension), $attribute->getAllowedExtensions()->normalize())) {
                            $this->context
                                ->buildViolation(EditMediaFileValueCommandConstraint::FILE_EXTENSION_NOT_ALLOWED_MESSAGE)
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
            if ($command->size > $this->getMaxFileSizeInByte($attribute)) {
                $this->context
                    ->buildViolation(EditMediaFileValueCommandConstraint::FILE_SIZE_EXCEEDED_MESSAGE)
                    ->atPath((string) $attribute->getCode())
                    ->addViolation();
            }
        }

        return $violations;
    }

    private function getMaxFileSizeInByte(MediaFileAttribute $attribute): float
    {
        return $attribute->getMaxFileSize()->floatValue() * 1000000;
    }
}

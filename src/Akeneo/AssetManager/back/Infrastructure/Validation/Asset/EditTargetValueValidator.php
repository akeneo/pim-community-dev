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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EditTargetValueValidator extends ConstraintValidator
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
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
        if (!$command instanceof AbstractEditValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', AbstractEditValueCommand::class,
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
        if (!$constraint instanceof EditTargetValue) {
            throw new UnexpectedTypeException($constraint, EditTargetValue::class);
        }
    }

    private function validateCommand(AbstractEditValueCommand $command): void
    {
        if ($this->attributeIsTargetOfATransformation($command)) {
            $this->context->buildViolation(EditTargetValue::TARGET_READONLY)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%attribute_code%', (string) $command->attribute->getCode())
                ->addViolation();
        }
    }

    private function attributeIsTargetOfATransformation(AbstractEditValueCommand $command): bool
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
}

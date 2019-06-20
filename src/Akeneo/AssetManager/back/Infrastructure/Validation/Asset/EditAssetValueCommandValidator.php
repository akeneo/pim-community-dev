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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditAssetValueCommand as EditAssetValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetValueCommandValidator extends ConstraintValidator
{
    /** @var AssetExistsInterface */
    private $assetExists;

    public function __construct(AssetExistsInterface $assetExists)
    {
        $this->assetExists = $assetExists;
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
        if (!$command instanceof EditAssetValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditAssetValueCommand::class,
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
        if (!$constraint instanceof EditAssetValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditAssetValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditAssetValueCommand $command): void
    {
        $assetsFound = $this->assetExists->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($command->attribute->getAssetType()->normalize()),
            AssetCode::fromString($command->assetCode)
        );

        if (!$assetsFound) {
            $this->context->buildViolation(EditAssetValueCommandConstraint::ERROR_MESSAGE)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%asset_code%', $command->assetCode)
                ->addViolation();
        }
    }
}

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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCollectionValueCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindExistingAssetCodesInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditAssetCollectionValueCommand as EditAssetCollectionValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCollectionValueCommandValidator extends ConstraintValidator
{
    /** @var FindExistingAssetCodesInterface */
    private $existingAssetCodes;

    public function __construct(FindExistingAssetCodesInterface $existingAssetCodes)
    {
        $this->existingAssetCodes = $existingAssetCodes;
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
        if (!$command instanceof EditAssetCollectionValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditAssetCollectionValueCommand::class,
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
        if (!$constraint instanceof EditAssetCollectionValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditAssetCollectionValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditAssetCollectionValueCommand $command): void
    {
        $foundAssets = $this->existingAssetCodes->find(
            AssetFamilyIdentifier::fromString($command->attribute->getAssetType()->normalize()),
            $command->assetCodes
        );

        $missingAssets = array_diff($command->assetCodes, $foundAssets);

        if (!empty($missingAssets)) {
            $this->context->buildViolation(EditAssetCollectionValueCommandConstraint::ERROR_MESSAGE)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%asset_codes%', implode(',', $missingAssets))
                ->addViolation();
        }
    }
}

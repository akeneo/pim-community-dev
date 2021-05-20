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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily\DeleteAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetFamilyShouldNotBeLinkedToAnyProductAttributeValidator extends ConstraintValidator
{
    private AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface $assetFamilyIsLinkedToProductAttributes;

    public function __construct(
        AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface $assetFamilyIsLinkedToProductAttributes
    ) {
        $this->assetFamilyIsLinkedToProductAttributes = $assetFamilyIsLinkedToProductAttributes;
    }

    public function validate($command, Constraint $constraint): void
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
        if (!$command instanceof DeleteAssetFamilyCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    DeleteAssetFamilyCommand::class,
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
        if (!$constraint instanceof AssetFamilyShouldNotBeLinkedToAnyProductAttribute) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(DeleteAssetFamilyCommand $command): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->identifier);
        $isLinkedToAtLeastOneProductAttribute = $this->assetFamilyIsLinkedToProductAttributes->isLinked($assetFamilyIdentifier);

        if ($isLinkedToAtLeastOneProductAttribute) {
            $this->context->buildViolation(AssetFamilyShouldNotBeLinkedToAnyProductAttribute::ERROR_MESSAGE)
                ->setParameter('%asset_family_identifier%', $assetFamilyIdentifier)
                ->addViolation();
        }
    }
}

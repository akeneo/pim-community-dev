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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AttributeAssetTypeAssetFamilyShouldExistValidator extends ConstraintValidator
{
    private AssetFamilyExistsInterface $assetFamilyExists;

    public function __construct(AssetFamilyExistsInterface $assetFamilyExists)
    {
        $this->assetFamilyExists = $assetFamilyExists;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->assetType);
        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            $this->context->buildViolation(AttributeAssetTypeAssetFamilyShouldExist::ERROR_MESSAGE)
                ->atPath('asset_family_code')
                ->setParameter('%asset_family_code%', $command->assetType)
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractCreateAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                AbstractCreateAttributeCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeAssetTypeAssetFamilyShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}

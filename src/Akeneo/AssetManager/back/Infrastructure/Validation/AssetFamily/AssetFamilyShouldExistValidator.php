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

use Akeneo\AssetManager\Application\Asset\IndexAssets\IndexAssetsByAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given asset_family already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyShouldExistValidator extends ConstraintValidator
{
    private AssetFamilyExistsInterface $assetFamilyExists;

    public function __construct(AssetFamilyExistsInterface $assetExists)
    {
        $this->assetFamilyExists = $assetExists;
    }

    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->validateCommand($command);
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AssetFamilyShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(IndexAssetsByAssetFamilyCommand $command): void
    {
        $assetFamilyIdentifier = $command->assetFamilyIdentifier;
        $alreadyExists = $this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        if (!$alreadyExists) {
            $this->context->buildViolation(AssetFamilyShouldExist::ERROR_MESSAGE)
                ->setParameter('%asset_family_code%', $assetFamilyIdentifier)
                ->atPath('code')
                ->addViolation();
        }
    }
}

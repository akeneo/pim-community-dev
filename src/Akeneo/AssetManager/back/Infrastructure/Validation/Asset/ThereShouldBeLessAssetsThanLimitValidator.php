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

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessAssetsThanLimitValidator extends ConstraintValidator
{
    private AssetRepositoryInterface $assetRepository;
    private int $assetsLimit;
    private CountAssetsInterface $countAssets;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        int $assetsLimit,
        CountAssetsInterface $countAssets
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetsLimit = $assetsLimit;
        $this->countAssets = $countAssets;
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
        if (!$command instanceof CreateAssetCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    CreateAssetCommand::class,
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
        if (!$constraint instanceof ThereShouldBeLessAssetsThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateAssetCommand $command): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier);

        $total = $this->countAssets->forAssetFamily($assetFamilyIdentifier);

        if ($total >= $this->assetsLimit) {
            $this->context->buildViolation(ThereShouldBeLessAssetsThanLimit::ERROR_MESSAGE)
                ->setParameter('%asset_label%', current($command->labels))
                ->setParameter('%limit%', $this->assetsLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}

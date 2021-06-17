<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionAssetNotFoundException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionValidationException;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConvention
{
    private AssetRepositoryInterface $assetRepository;

    private EditAssetCommandFactory $editAssetCommandFactory;

    private EditAssetHandler $editAssetHandler;

    private ValidatorInterface $validator;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator
    ) {
        $this->assetRepository = $assetRepository;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->validator = $validator;
    }

    public function executeOnAsset(AssetFamilyIdentifier $assetFamilyIdentifier, AssetIdentifier $assetIdentifier): void
    {
        try {
            $asset = $this->assetRepository->getByIdentifier($assetIdentifier);
        } catch (\Exception $exception) {
            throw new ExecuteNamingConventionAssetNotFoundException(
                $assetIdentifier,
                $exception
            );
        }

        $normalizedAsset = $asset->normalize();
        try {
            $editAssetCommand = $this->editAssetCommandFactory->create(
                [
                    'asset_family_identifier' => (string)$assetFamilyIdentifier,
                    'code' => $normalizedAsset['code'],
                    'values' => $normalizedAsset['values'],
                ]
            );
        } catch (\Exception $exception) {
            throw new ExecuteNamingConventionException($exception);
        }

        $violations = $this->validator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            throw new ExecuteNamingConventionValidationException($violations);
        }

        try {
            ($this->editAssetHandler)($editAssetCommand);
        } catch (\Exception $exception) {
            throw new ExecuteNamingConventionException($exception);
        }
    }
}

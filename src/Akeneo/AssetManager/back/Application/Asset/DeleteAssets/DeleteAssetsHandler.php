<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\DeleteAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAssetsHandler
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function __invoke(DeleteAssetsCommand $deleteAssetCommand): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($deleteAssetCommand->assetFamilyIdentifier);
        $assetCodes = array_map(fn ($code) => AssetCode::fromString($code), $deleteAssetCommand->assetCodes);

        $this->assetRepository->deleteByAssetFamilyAndCodes($assetFamilyIdentifier, $assetCodes);
    }
}

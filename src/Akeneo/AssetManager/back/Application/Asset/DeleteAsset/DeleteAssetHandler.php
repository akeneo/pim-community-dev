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

namespace Akeneo\AssetManager\Application\Asset\DeleteAsset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAssetHandler
{
    private AssetRepositoryInterface $assetRepository;

    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function __invoke(DeleteAssetCommand $deleteAssetCommand): void
    {
        $assetCode = AssetCode::fromString($deleteAssetCommand->assetCode);
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($deleteAssetCommand->assetFamilyIdentifier);

        $this->assetRepository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
    }
}

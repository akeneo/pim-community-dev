<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshAllAssets
{
    private SelectAssetIdentifiersInterface $selectAssetIdentifiers;

    private RefreshAsset $refreshAsset;

    public function __construct(
        SelectAssetIdentifiersInterface $selectAssetIdentifiers,
        RefreshAsset $refreshAsset
    ) {
        $this->selectAssetIdentifiers = $selectAssetIdentifiers;
        $this->refreshAsset = $refreshAsset;
    }

    public function execute(): void
    {
        $assetIdentifiers = $this->selectAssetIdentifiers->fetch();
        foreach ($assetIdentifiers as $assetIdentifier) {
            $this->refreshAsset->refresh($assetIdentifier);
        }
    }
}

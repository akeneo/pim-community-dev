<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;

interface SelectAssetIdentifiersInterface
{
    /**
     * @return AssetIdentifier[]
     */
    public function fetch(): \Iterator;
}

<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;

final class ExecuteNamingConventionAssetNotFoundException extends AbstractExecuteNamingConventionException
{
    private AssetIdentifier $assetIdentifier;

    public function __construct(
        AssetIdentifier $assetIdentifier,
        \Throwable $previous = null
    ) {
        parent::__construct('The asset was not found', 0, $previous);

        $this->assetIdentifier = $assetIdentifier;
    }

    public function getAssetIdentifier(): AssetIdentifier
    {
        return $this->assetIdentifier;
    }
}

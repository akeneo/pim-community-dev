<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;

class InMemoryCountAssets implements CountAssetsInterface
{
    private InMemoryAssetRepository $inMemoryAssetRepository;

    public function __construct(InMemoryAssetRepository $inMemoryAssetRepository)
    {
        $this->inMemoryAssetRepository = $inMemoryAssetRepository;
    }

    public function all(): int
    {
        return $this->inMemoryAssetRepository->count();
    }

    public function forAssetFamily(AssetFamilyIdentifier $identifierToMatch): int
    {
        return $this->inMemoryAssetRepository->countByAssetFamily($identifierToMatch);
    }
}

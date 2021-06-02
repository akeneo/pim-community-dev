<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;

class InMemoryFindIdentifiersByAssetFamilyAndCodes implements FindIdentifiersByAssetFamilyAndCodesInterface
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $identifiers = [];

        foreach ($this->assetRepository->all() as $asset) {
            if (
                $asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)
                && in_array($asset->getCode(), $assetCodes)
            ) {
                $identifiers[$asset->getCode()->normalize()] = $asset->getIdentifier();
            }
        }

        return $identifiers;
    }
}

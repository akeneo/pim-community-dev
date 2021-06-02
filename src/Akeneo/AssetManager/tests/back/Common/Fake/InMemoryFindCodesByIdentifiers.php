<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;

class InMemoryFindCodesByIdentifiers implements FindCodesByIdentifiersInterface
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers): array
    {
        $indexedCodes = [];

        /** @var Asset $asset */
        foreach ($this->assetRepository->all() as $asset) {
            $assetIdentifier = $asset->getIdentifier()->normalize();
            $assetCode = $asset->getCode()->normalize();

            if (in_array($assetIdentifier, $identifiers)) {
                $indexedCodes[$assetIdentifier] = $assetCode;
            }
        }

        return $indexedCodes;
    }
}

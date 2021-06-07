<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

class InMemoryFindAssetLabelsByIdentifiers implements FindAssetLabelsByIdentifiersInterface
{
    private InMemoryAssetRepository $assetRepository;

    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function __construct(
        InMemoryAssetRepository $assetRepository,
        InMemoryAssetFamilyRepository $assetFamilyRepository
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $assetIdentifiers): array
    {
        $assetLabels = [];
        foreach ($assetIdentifiers as $identifier) {
            $assetIdentifier = AssetIdentifier::fromString($identifier);
            $asset = $this->assetRepository->getByIdentifier($assetIdentifier);
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($asset->getAssetFamilyIdentifier());

            $valueKey = ValueKey::createFromNormalized(sprintf('%s_en_US', $assetFamily->getAttributeAsLabelReference()->normalize()));
            $value = $asset->findValue($valueKey);
            $labels[$value->getLocaleReference()->normalize()] = $value->getData()->normalize();
            $assetLabels[$assetIdentifier->normalize()] = [
                'labels' => $labels,
                'code' => $asset->getCode()->normalize()
            ];
        }

        return $assetLabels;
    }
}

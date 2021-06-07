<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByCodesInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryFindAssetLabelsByCodes implements FindAssetLabelsByCodesInterface
{
    private InMemoryAssetRepository $assetRepository;

    private InMemoryFindAssetFamilyAttributeAsLabel $findAssetFamilyAttributeAsLabel;

    public function __construct(
        InMemoryAssetRepository $assetRepository,
        InMemoryFindAssetFamilyAttributeAsLabel $findAssetFamilyAttributeAsLabel
    ) {
        $this->assetRepository = $assetRepository;
        $this->findAssetFamilyAttributeAsLabel = $findAssetFamilyAttributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $attributeAsLabel = $this->findAssetFamilyAttributeAsLabel->find($assetFamilyIdentifier)->normalize();
        $assets = $this->assetRepository->getByAssetFamilyAndCodes($assetFamilyIdentifier, $assetCodes);

        $labelCollectionPerAsset = [];
        /** @var Asset $asset */
        foreach ($assets as $asset) {
            $values = $asset->getValues()->normalize();
            $assetCode = $asset->getCode()->normalize();

            $labelsIndexedPerLocale = [];
            foreach ($values as $value) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $labelsIndexedPerLocale[$value['locale']] = $value['data'];
                }
            }

            $labelCollectionPerAsset[$assetCode] = LabelCollection::fromArray($labelsIndexedPerLocale);
        }

        return $labelCollectionPerAsset;
    }
}

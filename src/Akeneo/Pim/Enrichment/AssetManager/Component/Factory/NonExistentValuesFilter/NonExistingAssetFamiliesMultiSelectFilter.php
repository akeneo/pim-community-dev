<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\FindAllExistentAssetsForAssetFamilyIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingAssetFamiliesMultiSelectFilter implements NonExistentValuesFilter
{
    /** @var FindAllExistentAssetsForAssetFamilyIdentifiers */
    private $findAllExistentAssetsForAssetFamilyIdentifiers;

    public function __construct(FindAllExistentAssetsForAssetFamilyIdentifiers $findAllExistentAssetsForAssetFamilyIdentifiers)
    {
        $this->findAllExistentAssetsForAssetFamilyIdentifiers = $findAllExistentAssetsForAssetFamilyIdentifiers;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $assetCollectionValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AssetCollectionType::ASSET_COLLECTION);

        if (empty($assetCollectionValues)) {
            return $onGoingFilteredRawValues;
        }

        $assetCodes = $this->findExistentAssetCodesIndexedByAssetFamilyIdentifier($assetCollectionValues);

        $filteredValues = $this->buildRawValuesWithExistingAssetCodes($assetCollectionValues, $assetCodes);

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function findExistentAssetCodesIndexedByAssetFamilyIdentifier(array $assetCollectionValues): array
    {
        $assetCodesIndexedByAssetFamilyIdentifier = [];

        foreach ($assetCollectionValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $assetFamilyIdentifier = $productData['properties']['reference_data_name'];
                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $assetCodesIndexedByAssetFamilyIdentifier[$assetFamilyIdentifier][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueAssetCodesIndexedByAssetFamilyIdentifier = [];
        foreach ($assetCodesIndexedByAssetFamilyIdentifier as $assetFamilyIdentifier => $assetCodes) {
            $uniqueAssetCodesIndexedByAssetFamilyIdentifier[$assetFamilyIdentifier] = array_unique(array_merge(...$assetCodes));
        }

        $assetCodes = $this->findAllExistentAssetsForAssetFamilyIdentifiers->forAssetFamilyIdentifiersAndAssetCodes($uniqueAssetCodesIndexedByAssetFamilyIdentifier);

        return $assetCodes;
    }

    private function buildRawValuesWithExistingAssetCodes(array $assetCollectionValues, array $assetCodes): array
    {
        $filteredValues = [];

        foreach ($assetCollectionValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $multiSelectValues = [];
                $assetFamilyIdentifier = $productData['properties']['reference_data_name'];
                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = array_values(array_uintersect($value, $assetCodes[$assetFamilyIdentifier] ?? [], 'strcasecmp'));
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[AssetCollectionType::ASSET_COLLECTION][$attributeCode][] = [
                        'identifier' => $productData['identifier'],
                        'values' => $multiSelectValues,
                        'properties' => $productData['properties']
                    ];
                }
            }
        }

        return $filteredValues;
    }
}

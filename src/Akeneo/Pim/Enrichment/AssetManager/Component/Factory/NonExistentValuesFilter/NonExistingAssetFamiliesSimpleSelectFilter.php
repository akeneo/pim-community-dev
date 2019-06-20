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

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetSingleLinkType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\FindAllExistentAssetsForAssetFamilyIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingAssetFamiliesSimpleSelectFilter implements NonExistentValuesFilter
{
    /** @var FindAllExistentAssetsForAssetFamilyIdentifiers */
    private $findAllExistentAssetsForAssetFamilyIdentifiers;

    public function __construct(FindAllExistentAssetsForAssetFamilyIdentifiers $findAllExistentAssetsForAssetFamilyIdentifiers)
    {
        $this->findAllExistentAssetsForAssetFamilyIdentifiers = $findAllExistentAssetsForAssetFamilyIdentifiers;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $singleAssetLinkValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AssetSingleLinkType::ASSET_SINGLE_LINK);

        if (empty($singleAssetLinkValues)) {
            return $onGoingFilteredRawValues;
        }

        $assetCodesFromRawValues = $this->getAllAssetCodesFromRawValues($singleAssetLinkValues);

        $existentAssetCodes = $this->findAllExistentAssetsForAssetFamilyIdentifiers->forAssetFamilyIdentifiersAndAssetCodes($assetCodesFromRawValues);

        $filteredValues = $this->removeNonExistentAssetCodesFromValues($singleAssetLinkValues, $existentAssetCodes);

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function removeNonExistentAssetCodesFromValues(array $singleAssetLinkValues, array $assetCodes): array
    {
        $filteredValues = [];

        foreach ($singleAssetLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $singleLinkValues = [];
                $assetFamilyIdentifier = $productValues['properties']['reference_data_name'];
                if (!isset($assetCodes[$assetFamilyIdentifier])) {
                    continue;
                }
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (!is_array($value)) {
                            if (in_array($value, $assetCodes[$assetFamilyIdentifier])) {
                                $singleLinkValues[$channel][$locale] = $value;
                            } else {
                                $singleLinkValues[$channel][$locale] = '';
                            }
                        }
                    }
                }

                if ($singleLinkValues !== []) {
                    $filteredValues[AssetSingleLinkType::ASSET_SINGLE_LINK][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $singleLinkValues,
                        'properties' => $productValues['properties']
                    ];
                }
            }
        }

        return $filteredValues;
    }

    private function getAllAssetCodesFromRawValues(array $singleAssetLinkValues): array
    {
        $assetCodesIndexedByAssetFamilyIdentifier = [];

        foreach ($singleAssetLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $assetFamilyIdentifier = $productValues['properties']['reference_data_name'];
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (!is_array($value) && $value !== null) {
                            $assetCodesIndexedByAssetFamilyIdentifier[$assetFamilyIdentifier][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueAssetCodesIndexedByAssetFamilyIdentifier = [];
        foreach ($assetCodesIndexedByAssetFamilyIdentifier as $assetFamilyIdentifier => $assetCodes) {
            $uniqueAssetCodesIndexedByAssetFamilyIdentifier[$assetFamilyIdentifier] = array_unique($assetCodes);
        }

        return $uniqueAssetCodesIndexedByAssetFamilyIdentifier;
    }
}

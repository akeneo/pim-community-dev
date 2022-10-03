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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingReferenceEntitiesMultiSelectFilter implements NonExistentValuesFilter
{
    private FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers;

    public function __construct(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $this->findAllExistentRecordsForReferenceEntityIdentifiers = $findAllExistentRecordsForReferenceEntityIdentifiers;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $multipleRecordLinkValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);

        if (empty($multipleRecordLinkValues)) {
            return $onGoingFilteredRawValues;
        }

        $recordCodes = $this->findExistentRecordCodesIndexedByReferenceEntityIdentifier($multipleRecordLinkValues);

        $filteredValues = $this->buildRawValuesWithExistingRecordCodes($multipleRecordLinkValues, $recordCodes);

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function findExistentRecordCodesIndexedByReferenceEntityIdentifier(array $multipleRecordLinkValues): array
    {
        $recordCodesIndexedByReferenceEntityIdentifier = [];

        foreach ($multipleRecordLinkValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $referenceEntityIdentifier = $productData['properties']['reference_data_name'];
                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $recordCodesIndexedByReferenceEntityIdentifier[$referenceEntityIdentifier][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueRecordCodesIndexedByReferenceEntityIdentifier = [];
        foreach ($recordCodesIndexedByReferenceEntityIdentifier as $referenceEntityIdentifier => $recordCodes) {
            $uniqueRecordCodesIndexedByReferenceEntityIdentifier[$referenceEntityIdentifier] = array_unique(array_merge(...$recordCodes));
        }

        $recordCodes = $this->findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($uniqueRecordCodesIndexedByReferenceEntityIdentifier);

        return $recordCodes;
    }

    private function buildRawValuesWithExistingRecordCodes(array $multipleRecordLinkValues, array $recordCodes): array
    {
        $filteredValues = [];

        foreach ($multipleRecordLinkValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $multiSelectValues = [];
                $referenceEntityIdentifier = strtolower($productData['properties']['reference_data_name']);

                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = array_values(array_uintersect($value, $recordCodes[$referenceEntityIdentifier] ?? [], 'strcasecmp'));
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION][$attributeCode][] = [
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

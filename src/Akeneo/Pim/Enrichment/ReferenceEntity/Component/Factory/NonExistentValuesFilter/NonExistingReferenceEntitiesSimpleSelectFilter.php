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
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class NonExistingReferenceEntitiesSimpleSelectFilter implements NonExistentValuesFilter
{
    private FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers;

    public function __construct(FindAllExistentRecordsForReferenceEntityIdentifiers $findAllExistentRecordsForReferenceEntityIdentifiers)
    {
        $this->findAllExistentRecordsForReferenceEntityIdentifiers = $findAllExistentRecordsForReferenceEntityIdentifiers;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $singleRecordLinkValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(ReferenceEntityType::REFERENCE_ENTITY);

        if (empty($singleRecordLinkValues)) {
            return $onGoingFilteredRawValues;
        }

        $recordCodesFromRawValues = $this->getAllRecordCodesFromRawValues($singleRecordLinkValues);

        $existentRecordCodes = $this->findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($recordCodesFromRawValues);

        $filteredValues = $this->removeNonExistentRecordCodesFromValues($singleRecordLinkValues, $existentRecordCodes);

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function removeNonExistentRecordCodesFromValues(array $singleRecordLinkValues, array $recordCodes): array
    {
        $filteredValues = [];

        foreach ($singleRecordLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $singleLinkValues = [];
                $referenceEntityIdentifier = strtolower($productValues['properties']['reference_data_name']);
                $existingRecordCodesForAttribute = array_map('strtolower', $recordCodes[$referenceEntityIdentifier] ?? []);

                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (!is_array($value)) {
                            if (null !== $value && in_array(strtolower($value), $existingRecordCodesForAttribute)) {
                                $singleLinkValues[$channel][$locale] = $value;
                            } else {
                                $singleLinkValues[$channel][$locale] = '';
                            }
                        }
                    }
                }

                if ($singleLinkValues !== []) {
                    $filteredValues[ReferenceEntityType::REFERENCE_ENTITY][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $singleLinkValues,
                        'properties' => $productValues['properties']
                    ];
                }
            }
        }

        return $filteredValues;
    }

    private function getAllRecordCodesFromRawValues(array $singleRecordLinkValues): array
    {
        $recordCodesIndexedByReferenceEntityIdentifier = [];

        foreach ($singleRecordLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $referenceEntityIdentifier = strtolower($productValues['properties']['reference_data_name']);
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (!is_array($value) && $value !== null) {
                            $recordCodesIndexedByReferenceEntityIdentifier[$referenceEntityIdentifier][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueRecordCodesIndexedByReferenceEntityIdentifier = [];
        foreach ($recordCodesIndexedByReferenceEntityIdentifier as $referenceEntityIdentifier => $recordCodes) {
            $uniqueRecordCodesIndexedByReferenceEntityIdentifier[$referenceEntityIdentifier] = array_unique($recordCodes);
        }

        return $uniqueRecordCodesIndexedByReferenceEntityIdentifier;
    }
}

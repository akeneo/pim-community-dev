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
    /** @var FindAllExistentRecordsForReferenceEntityIdentifiers */
    private $findAllExistentRecordsForReferenceEntityIdentifiers;

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

        $recordCodesIndexedByReferenceEntityIdentifier = [];

        foreach ($multipleRecordLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $referenceEntityIdentifier = $productValues['properties']['reference_data_name'];
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
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

        $filteredValues = [];

        foreach ($multipleRecordLinkValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $multiSelectValues = [];
                $referenceEntityIdentifier = $productValues['properties']['reference_data_name'];
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = array_intersect($value, $recordCodes[$referenceEntityIdentifier] ?? []);
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $multiSelectValues,
                        'properties' => $productValues['properties']
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }
}

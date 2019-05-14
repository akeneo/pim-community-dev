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
use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Enrichment\FindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;

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
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $recordCodes = $this->findAllExistentRecordsForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes($selectValues);

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productData) {
            foreach ($productData as $productValues) {
                $multiSelectValues = [];
                foreach ($productValues['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = array_intersect($value, $recordCodes[$attributeCode] ?? []);
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

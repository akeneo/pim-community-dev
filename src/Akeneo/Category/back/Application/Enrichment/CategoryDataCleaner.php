<?php

declare(strict_types=1);

namespace Akeneo\Category\back\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryDataFilter;
use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryDataCleaner
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedCategoryValuesOrderedByCategoryCode $getEnrichedCategoryValuesOrderedByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    /**
     * @param array<string> $filteringKeys
     */
    public function __invoke(array $filteringKeys, CategoryDataFilter $filter): void
    {
        $offset = 0;
        $cleanedBatch = [];

        do {
            $valuesByCode = $this->getEnrichedCategoryValuesOrderedByCategoryCode->byLimitAndOffset(self::CATEGORY_BATCH_SIZE, $offset);
            $offset += self::CATEGORY_BATCH_SIZE;

            foreach ($valuesByCode as $categoryCode => $json) {
                $enrichedValues = json_decode($json, true);
                $valueKeysToRemove = $filter->filterCategoryToClean($enrichedValues, $filteringKeys);
                if (!empty($valueKeysToRemove)) {
                    foreach ($valueKeysToRemove as $key) {
                        unset($enrichedValues[$key]);
                    }
                    $cleanedBatch[$categoryCode] = json_encode($enrichedValues);
                }

                if (\count($cleanedBatch) >= self::CATEGORY_BATCH_SIZE) {
                    $this->updateCategoryEnrichedValues->execute($cleanedBatch);
                    $cleanedBatch = [];
                }
            }

            // no new enriched values are found in database and there are still cleaned values to update
            if (empty($valuesByCode) && !empty($cleanedBatch)) {
                $this->updateCategoryEnrichedValues->execute($cleanedBatch);
            }
        } while (!empty($valuesByCode));
    }

    private function removeAttributeKey(array $enrichedValues, $key) {
        // TODO remove key from enriched values + manage deletion of media file linked to attributes with a media type (i.e. image)
    }
}

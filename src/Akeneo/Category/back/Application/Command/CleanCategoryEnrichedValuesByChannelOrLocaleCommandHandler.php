<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Enrichment\Filter\ByChannelAndLocalesFilter;
use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedCategoryValuesOrderedByCategoryCode $getEnrichedCategoryValuesOrderedByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    )
    {
    }

    public function __invoke(CleanCategoryEnrichedValuesByChannelOrLocaleCommand $command): void
    {
        $offset = 0;
        $cleanedBatch = [];

        do {
            $valuesByCode = $this->getEnrichedCategoryValuesOrderedByCategoryCode->byLimitAndOffset(self::CATEGORY_BATCH_SIZE, $offset);
            $offset += self::CATEGORY_BATCH_SIZE;

            foreach ($valuesByCode as $categoryCode => $json) {
                $enrichedValues = json_decode($json, true);
                $valueKeysToRemove = ByChannelAndLocalesFilter::getEnrichedValueCompositeKeysToClean(
                    $enrichedValues,
                    $command->channelCode,
                    $command->localeCodes
                );
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
}

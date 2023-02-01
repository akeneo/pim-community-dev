<?php

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class CleanCategoryDataLinkedToChannel
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedCategoryValuesOrderedByCategoryCode $getEnrichedCategoryValuesOrderedByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    public function __invoke(string $channelCode): void
    {
        $offset = 0;
        $cleanedBatch = [];

        do {
            $valuesByCode = $this->getEnrichedCategoryValuesOrderedByCategoryCode->byLimitAndOffset(self::CATEGORY_BATCH_SIZE, $offset);
            $offset += self::CATEGORY_BATCH_SIZE;

            foreach ($valuesByCode as $categoryCode => $json) {
                $enrichedValues = json_decode($json, true);
                $valueKeysToRemove = $this->getEnrichedValueKeysToRemove($enrichedValues, $channelCode);
                if (!empty($valueKeysToRemove)) {
                    foreach ($valueKeysToRemove as $key) {
                        unset($enrichedValues[$key]);
                    }
                    $cleanedBatch[$categoryCode] = json_encode($enrichedValues);
                }

                if (\count($cleanedBatch) >= self::CATEGORY_BATCH_SIZE) {
                    $this->updateCategoriesBatch($cleanedBatch);
                    $cleanedBatch = [];
                }
            }

            // no new enriched values are found in database and there are still cleaned values to update
            if (empty($valuesByCode) && !empty($cleanedBatch)) {
                $this->updateCategoriesBatch($cleanedBatch);
            }
        } while (!empty($valuesByCode));
    }

    /**
     * @param array<string, string> $values
     *
     * @return array<string>
     */
    private function getEnrichedValueKeysToRemove(array $values, string $code): array
    {
        $keysToRemove = [];

        // matching string is '.*\|.*\|$code\|.*'
        $matchingString = '.*\\'.AbstractValue::SEPARATOR.'.*\\'.AbstractValue::SEPARATOR.$code.'\|.*';
        foreach ($values as $key => $value) {
            if ($key === 'attribute_codes') {
                continue;
            }
            if (preg_match('/'.$matchingString.'/', $key, $matches)) {
                $keysToRemove[] = $key;
            }
        }

        return $keysToRemove;
    }

    /**
     * @param array<string, string> $batch
     */
    private function updateCategoriesBatch(array $batch): void
    {
        $this->updateCategoryEnrichedValues->execute($batch);
    }
}

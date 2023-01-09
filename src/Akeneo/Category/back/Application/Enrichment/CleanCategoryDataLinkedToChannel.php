<?php

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Query\GetAllEnrichedCategoryValuesByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class CleanCategoryDataLinkedToChannel
{
    public function __construct(
        private readonly GetAllEnrichedCategoryValuesByCategoryCode $getAllEnrichedCategoryValuesByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    public function __invoke(string $channelCode): void
    {
        $valuesByCode = $this->getAllEnrichedCategoryValuesByCategoryCode->execute();
        $batch = [];

        foreach ($valuesByCode as $categoryCode => $json) {
            $enrichedValues = json_decode($json, true);
            $valueKeysToRemove = $this->getEnrichedValueKeysToRemove($enrichedValues, $channelCode);
            if (!empty($valueKeysToRemove)) {
                foreach ($valueKeysToRemove as $key) {
                    unset($enrichedValues[$key]);
                }
                $batch[$categoryCode] = json_encode($enrichedValues);
            }

            if (\count($batch) >= 100) {
                $this->updateCategoriesBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->updateCategoriesBatch($batch);
        }
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

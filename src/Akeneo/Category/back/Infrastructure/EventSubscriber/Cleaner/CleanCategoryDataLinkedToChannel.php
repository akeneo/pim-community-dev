<?php

namespace Akeneo\Category\Infrastructure\EventSubscriber\Cleaner;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql\GetAllEnrichedCategoryValuesByCategoryCode;
use Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql\UpdateCategoryEnrichedValues;

class CleanCategoryDataLinkedToChannel
{
    public function __construct(
        private readonly GetAllEnrichedCategoryValuesByCategoryCode $getAllEnrichedCategoryValuesByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    )
    {
    }

    public function __invoke(string $channelCode): void
    {
        $valuesByCode = $this->getAllEnrichedCategoryValuesByCategoryCode->execute();

        foreach($valuesByCode as $categoryCode => $json) {
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

    private function getEnrichedValueKeysToRemove(array $values, string $code): array
    {
        $keysToRemove = [];

        // matching string is '.*\|.*\|$code\|.*'
        $matchingString = '.*\\'.AbstractValue::SEPARATOR.'.*\\'.AbstractValue::SEPARATOR . $code . '\|.*';

        foreach ($values as $key => $value) {
            if (preg_match('/'.$matchingString.'/', $key, $matches)) {
                $keysToRemove[] = $key;
            }
        }
        return $keysToRemove;
    }


    private function updateCategoriesBatch(array $batch): void
    {
        $this->updateCategoryEnrichedValues->execute($batch);
    }
}

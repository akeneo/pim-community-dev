<?php

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;

class CleanCategoryDataLinkedToChannel
{
    public const CLEAN_CHANNEL_ACTION = 'cleanChannel';
    public const CLEAN_CHANNEL_LOCALE_ACTION = 'cleanChannelLocale';
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedCategoryValuesOrderedByCategoryCode $getEnrichedCategoryValuesOrderedByCategoryCode,
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    public function __invoke(ChannelInterface $channel, string $action): void
    {
        $offset = 0;
        $cleanedBatch = [];

        do {
            $valuesByCode = $this->getEnrichedCategoryValuesOrderedByCategoryCode->byLimitAndOffset(self::CATEGORY_BATCH_SIZE, $offset);
            $offset += self::CATEGORY_BATCH_SIZE;

            foreach ($valuesByCode as $categoryCode => $json) {
                $enrichedValues = json_decode($json, true);
                $valueKeysToRemove = $this->getEnrichedValueKeysToRemove($enrichedValues, $channel, $action);
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
    private function getEnrichedValueKeysToRemove(array $values, ChannelInterface $channel, string $action): array
    {
        $keysToRemove = [];
        $codes = array_map(function (Locale $locale) {
            return $locale->getCode();
        },$channel->getLocales()->getValues());
        // matching string is '.*\|.*\|$code\|.*'
        $matchingString = '.*\\'.AbstractValue::SEPARATOR.'.*\\'.AbstractValue::SEPARATOR.$channel->getCode().'\|(\w{2}_\w{2})';
        foreach ($values as $key => $value) {
            if ($key === 'attribute_codes') {
                continue;
            }
            if($action == self::CLEAN_CHANNEL_ACTION) {
                if (preg_match('/'.$matchingString.'/', $key, $matches)) {
                    $keysToRemove[] = $key;
                }
            } else if($action == self::CLEAN_CHANNEL_LOCALE_ACTION) {
                if(preg_match('/'.$matchingString.'/', $key, $matches)) {
                    if(!in_array($matches[1], $codes)) {
                        $keysToRemove[] = $key;
                    }
                }
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

<?php

namespace Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

class ByChannelAndLocalesFilter
{
    /**
     * @param array<string> $localeCodes
     *
     * @return array<Value>
     */
    public static function getEnrichedValuesToClean(
        ValueCollection $enrichedValues,
        string $channelCode,
        array $localeCodes,
    ): array {
        $valuesToRemove = [];
        if ($channelCode === '') {
            return [];
        }

        foreach ($enrichedValues as $enrichedValue) {
            /** @var Value $enrichedValue */
            $valueChannel = $enrichedValue->getChannel();
            if (empty($localeCodes)) {
                // we do not clean non-scopable values
                if ($valueChannel !== null && (string) $valueChannel === $channelCode) {
                    $valuesToRemove[] = $enrichedValue;
                }
            } else {
                $valueLocale = $enrichedValue->getLocale();
                // we do not clean non-localizable values
                if (
                    $valueLocale !== null
                    && (string) $valueChannel === $channelCode
                    && !in_array((string) $valueLocale, $localeCodes, true)
                ) {
                    $valuesToRemove[] = $enrichedValue;
                }
            }
        }

        return $valuesToRemove;
    }
}

<?php

namespace Akeneo\Category\Application\Enrichment\Filter;

class ByChannelAndLocalesFilter
{
    /**
     * @param array<string, array> $enrichedValues
     * @param array<string, mixed> $filteringKeys
     * @return array<string>
     */
    public static function getEnrichedValueCompositeKeysToClean(
        array $enrichedValues,
        string $channelCode,
        ?array $localeCodes
    ): array
    {
        $keysToRemove = [];
        if ($channelCode === '' || $channelCode === null) {
            return [];
        }

        foreach ($enrichedValues as $key => $value) {
            if ($key === 'attribute_codes') {
                continue;
            }
            $valueChannel = $value['channel']?? null;
            if (empty($localeCodes)) {
                // we do not clean non-scopable values
                if ($valueChannel !== null && $valueChannel === $channelCode) {
                    $keysToRemove[] = $key;
                }
            } else {
                $valueLocale = $value['locale'];
                // we do not clean non-localizable values
                if (
                    $valueLocale !== null
                    && $valueChannel === $channelCode
                    && !in_array($valueLocale, $localeCodes)
                ) {
                    $keysToRemove[] = $key;
                }
            }
        }
        return $keysToRemove;
    }
}

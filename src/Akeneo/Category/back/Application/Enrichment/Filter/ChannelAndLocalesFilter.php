<?php

namespace Akeneo\Category\Application\Enrichment\Filter;

class ChannelAndLocalesFilter implements CategoryDataFilter
{
    public const CLEAN_CHANNEL_ACTION = 'cleanChannel';
    public const CLEAN_CHANNEL_LOCALE_ACTION = 'cleanChannelLocale';

    /**
     * @param array<string, array> $enrichedValues
     * @param array<string, mixed> $filteringKeys
     * @return array<string>
     */
    public function filterCategoryToClean(array $enrichedValues, array $filteringKeys): array
    {
        $keysToRemove = [];
        $channelCode = $filteringKeys['channel_code'];
        if ($channelCode === '' || $channelCode === null) {
            return [];
        }
        $action = $filteringKeys['action'] ?? null;

        foreach ($enrichedValues as $key => $value) {
            if ($key === 'attribute_codes') {
                continue;
            }
            $valueChannel = $value['channel']?? null;
            if ($action == self::CLEAN_CHANNEL_ACTION) {
                // we do not clean not scopable values
                if ($valueChannel !== null && $valueChannel === $channelCode) {
                    $keysToRemove[] = $key;
                }
            } elseif ($action == self::CLEAN_CHANNEL_LOCALE_ACTION) {
                $channelLocales = $filteringKeys['locales_codes'];
                $valueLocale = $value['locale'];
                // we do not clean not localizable values
                if (
                    $valueLocale !== null
                    && $valueChannel === $channelCode
                    && !in_array($valueLocale, $channelLocales)
                ) {
                    $keysToRemove[] = $key;
                }
            }
        }
        return $keysToRemove;
    }
}

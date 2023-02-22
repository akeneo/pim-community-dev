<?php

namespace Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class ChannelAndLocalesFilter implements CategoryDataFilter
{
    public const CLEAN_CHANNEL_ACTION = 'cleanChannel';
    public const CLEAN_CHANNEL_LOCALE_ACTION = 'cleanChannelLocale';

    public function __construct(
    ) {
    }

    /**
     * @param array<string, string> $enrichedValues
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

        // TODO find better regex to find channel in these keys:
        // TODO - attribute_code|attribute_uuid|channel_code|locale_code
        // TODO - attribute_code|attribute_uuid|channel_code
        // TODO - attribute_code|attribute_uuid|locale_code
        // TODO - quid of the action to do when the channel is not scopable? check all locales?
        $matchingString = '.*\\'.AbstractValue::SEPARATOR.'.*\\'.AbstractValue::SEPARATOR.$channelCode.'\\'.AbstractValue::SEPARATOR.'(\w{2}_\w{2})';
        foreach ($enrichedValues as $key => $value) {
            if ($key === 'attribute_codes') {
                continue;
            }
            if ($action == self::CLEAN_CHANNEL_ACTION) {
                if (preg_match('/' . $matchingString . '/', $key, $matches)) {
                    $keysToRemove[] = $key;
                }
            } elseif ($action == self::CLEAN_CHANNEL_LOCALE_ACTION) {
                $channelLocales = $filteringKeys['locales_codes'];
                if (
                    preg_match('/'.$matchingString.'/', $key, $matches)
                    && !in_array($matches[1], $channelLocales)
                ) {
                    $keysToRemove[] = $key;
                }
            }
        }
        return $keysToRemove;
    }
}

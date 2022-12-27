<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentChannelLocaleValuesFilter implements NonExistentValuesFilter
{
    public function __construct(
        private ChannelExistsWithLocaleInterface $channelsLocales,
        private GetAttributes $getAttributes
    ) {
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $filteredRawValues = [];
        foreach ($onGoingFilteredRawValues->nonFilteredRawValuesCollectionIndexedByType() as $type => $rawValuesIndexedByAttribute) {
            $filteredRawValues[$type] = [];
            foreach ($rawValuesIndexedByAttribute as $attributeCode => $rawValuesIndexedByProduct) {
                foreach ($rawValuesIndexedByProduct as $productIndex => $productValues) {
                    $productValues['values'] = $this->filterProductValues($productValues['values']);
                    $productValues['values'] = $this->filterLocaleSpecificProductValues($productValues['values'], (string) $attributeCode);
                    $filteredRawValues[$type][$attributeCode][$productIndex] = $productValues;
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredRawValues);
    }

    private function filterProductValues(array $productValues): array
    {
        $filteredProductValues = [];
        foreach ($productValues as $channel => $localeValues) {
            if ($this->doesChannelExist($channel)) {
                foreach ($localeValues as $locale => $value) {
                    if ($this->isLocaleActivatedForChannel($locale, $channel)) {
                        if ($locale === '<all_locales>') {
                            $filteredProductValues[$channel][$locale] = $value;
                        } else {
                            $filteredProductValues[$channel][$this->channelsLocales->getLocaleNameWithRightCase($locale)] = $value;
                        }
                    }
                }
            }
        }

        return $filteredProductValues;
    }

    private function filterLocaleSpecificProductValues(array $productValues, string $attributeCode): array
    {
        $filteredProductValues = [];
        $attribute = $this->getAttributes->forCode($attributeCode);

        foreach ($productValues as $channel => $localeValues) {
            foreach ($localeValues as $localeCode => $value) {
                if (!$attribute->isLocaleSpecific() || $localeCode === '<all_locales>' || in_array($localeCode, $attribute->availableLocaleCodes())) {
                    $filteredProductValues[$channel][$localeCode] = $value;
                }
            }
        }

        return $filteredProductValues;
    }

    private function doesChannelExist(string $channel): bool
    {
        return $channel === '<all_channels>' || $this->channelsLocales->doesChannelExist($channel);
    }

    private function isLocaleActivatedForChannel(string $locale, string $channel): bool
    {
        return $locale === '<all_locales>'
            || (
                $channel === '<all_channels>'
                    ? $this->channelsLocales->isLocaleActive($locale)
                    : $this->channelsLocales->isLocaleBoundToChannel($locale, $channel)
            );
    }
}

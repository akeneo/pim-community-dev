<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleDataCollection
{
    private array $channelLocaleData = [];

    public static function fromNormalizedChannelLocaleData(array $normalizedChannelLocaleData, \Closure $callback): self
    {
        $channelLocaleData = [];
        foreach ($normalizedChannelLocaleData as $channel => $localeData) {
            foreach ($localeData as $locale => $data) {
                $channelLocaleData[$channel][$locale] = $callback($data);
            }
        }

        $channelLocaleDataCollection = new self();
        $channelLocaleDataCollection->channelLocaleData = $channelLocaleData;

        return $channelLocaleDataCollection;
    }

    public function mapWith(\Closure $callback): array
    {
        $mappedData = [];
        foreach ($this->channelLocaleData as $channel => $localeData) {
            foreach ($localeData as $locale => $data) {
                $mappedData[$channel][$locale] = $callback($data);
            }
        }

        return $mappedData;
    }
}

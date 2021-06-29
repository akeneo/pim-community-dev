<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleDataCollection implements \IteratorAggregate
{
    /** @var array */
    private $channelLocaleData;

    public function __construct()
    {
        $this->channelLocaleData = [];
    }

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

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->channelLocaleData);
    }

    public function toArray(): array
    {
        return $this->channelLocaleData;
    }

    public function getByChannelAndLocale(ChannelCode $channel, LocaleCode $locale)
    {
        return $this->channelLocaleData[strval($channel)][strval($locale)] ?? null;
    }

    public function addToChannelAndLocale(ChannelCode $channel, LocaleCode $locale, $data): self
    {
        $this->channelLocaleData[strval($channel)][strval($locale)] = $data;

        return $this;
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

    public function isEmpty(): bool
    {
        return empty($this->channelLocaleData);
    }
}

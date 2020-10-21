<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleDataScalarCollection implements \IteratorAggregate
{
    /** @var array */
    private $channelLocaleData;

    static public function filledWith(array $localesByChannel, \Closure $callback): self
    {
        $channelLocaleData = [];
        foreach ($localesByChannel as $channel => $locales) {
            foreach ($locales as $locale) {
                $channelLocaleData[$channel][$locale] = $callback($channel, $locale);
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
}

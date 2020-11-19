<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class ChannelLocaleRateCollection implements \IteratorAggregate
{
    /** @var ChannelLocaleDataCollection */
    private $rates;

    public function __construct()
    {
        $this->rates = new ChannelLocaleDataCollection();
    }

    public function addRate(ChannelCode $channelCode, LocaleCode $localeCode, Rate $rate): self
    {
        $this->rates = $this->rates->addToChannelAndLocale($channelCode, $localeCode, $rate);

        return $this;
    }

    public function getByChannelAndLocale(ChannelCode $channel, LocaleCode $locale): ?Rate
    {
        return $this->rates->getByChannelAndLocale($channel, $locale);
    }

    public static function fromArrayInt(array $rawRates): self
    {
        $rateCollection = new self();

        $rateCollection->rates = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData(
            $rawRates, function ($rawRate) {
                return new Rate(intval($rawRate));
            });

        return $rateCollection;
    }

    public static function fromNormalizedRates(array $normalizedRates, \Closure $getNormalizedRateValue): self
    {
        $rateCollection = new self();

        $rateCollection->rates = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData(
            $normalizedRates,
            function ($normalizedRate) use ($getNormalizedRateValue) {
                return new Rate(intval($getNormalizedRateValue($normalizedRate)));
            });

        return $rateCollection;
    }

    public function toArrayLetter(): array
    {
        return $this->rates->mapWith(function (Rate $rate) {
            return $rate->toLetter();
        });
    }

    public function toArrayInt(): array
    {
        return $this->rates->mapWith(function (Rate $rate) {
            return $rate->toInt();
        });
    }

    public function toArrayIntRank(): array
    {
        return $this->rates->mapWith(fn (Rate $rate) => Rank::fromRate($rate)->toInt());
    }

    public function mapWith(\Closure $callback): array
    {
        return $this->rates->mapWith($callback);
    }

    public function getIterator(): \Iterator
    {
        return $this->rates->getIterator();
    }
}

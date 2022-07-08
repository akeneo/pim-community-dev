<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleRateCollection implements \IteratorAggregate
{
    private ChannelLocaleDataCollection $rates;

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
            $rawRates,
            function ($rawRate) {
                return new Rate(intval($rawRate));
            }
        );

        return $rateCollection;
    }

    /**
     * Format of a normalized of a Rate:
     *  [
     *      'rank'  => int, // Rank of the rate (from 1 to 5)
     *      'value' => int, // Raw value (from 0 to 100)
     *  ]
     * @param array<string, array<string, array{rank: int, value: int}>> $normalizedRates
     */
    public static function fromNormalizedRates(array $normalizedRates): self
    {
        $rateCollection = new self();

        $rateCollection->rates = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData(
            $normalizedRates,
            function (array $normalizedRate) {
                Assert::keyExists($normalizedRate, 'value', 'The normalized rate is malformed');
                return new Rate(intval($normalizedRate['value']));
            }
        );

        return $rateCollection;
    }

    public function isEmpty(): bool
    {
        return $this->rates->isEmpty();
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

    /**
     * @return array<string, array<string, array{rank: int, value: int}>>
     **/
    public function toNormalizedRates(): array
    {
        return $this->rates->mapWith(function (Rate $score) {
            return [
                'rank' => Rank::fromRate($score)->toInt(),
                'value' => $score->toInt(),
            ];
        });
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

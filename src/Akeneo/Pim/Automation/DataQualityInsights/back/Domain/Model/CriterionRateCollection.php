<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionRateCollection implements \IteratorAggregate
{
    /** @var array [channel_code => [locale_code => rate]] */
    private $rates = [];

    public function addRate(ChannelCode $channelCode, LocaleCode $localeCode, Rate $rate): self
    {
        $this->rates[strval($channelCode)][strval($localeCode)] = $rate;

        return $this;
    }

    public function getByChannelAndLocale(ChannelCode $channelCode, LocaleCode $localeCode): ?Rate
    {
        return $this->rates[strval($channelCode)][strval($localeCode)] ?? null;
    }

    public function toArrayInt(): array
    {
        return array_map(function ($ratesPerLocale) {
            return array_map(function ($rate) {
                return $rate->toInt();
            }, $ratesPerLocale);
        }, $this->rates);
    }

    public function toArrayString(): array
    {
        return array_map(function ($ratesPerLocale) {
            return array_map(function ($rate) {
                return strval($rate);
            }, $ratesPerLocale);
        }, $this->rates);
    }

    public static function fromArray(array $rawRates): self
    {
        $rates = new self();
        foreach ($rawRates as $channel => $ratesPerLocale) {
            $channelCode = new ChannelCode($channel);
            foreach ($ratesPerLocale as $locale => $rate) {
                $rates->addRate($channelCode, new LocaleCode($locale), new Rate(intval($rate)));
            }
        }

        return $rates;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->rates);
    }
}

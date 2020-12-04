<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CompletenessCalculationResult
{
    private ChannelLocaleRateCollection $rates;

    private ChannelLocaleDataCollection $missingAttributes;

    private ChannelLocaleDataCollection $totalNumberOfAttributes;

    public function __construct()
    {
        $this->rates = new ChannelLocaleRateCollection();
        $this->missingAttributes = new ChannelLocaleDataCollection();
        $this->totalNumberOfAttributes = new ChannelLocaleDataCollection();
    }

    public function getRates(): ChannelLocaleRateCollection
    {
        return $this->rates;
    }

    public function getMissingAttributes(): ChannelLocaleDataCollection
    {
        return $this->missingAttributes;
    }

    public function addRate(ChannelCode $channelCode, LocaleCode $localeCode, Rate $rate): self
    {
        $this->rates->addRate($channelCode, $localeCode, $rate);

        return $this;
    }

    public function addMissingAttributes(ChannelCode $channelCode, LocaleCode $localeCode, array $missingAttributes): self
    {
        $this->missingAttributes->addToChannelAndLocale($channelCode, $localeCode, $missingAttributes);

        return $this;
    }

    public function addTotalNumberOfAttributes(ChannelCode $channelCode, LocaleCode $localeCode, int $number): self
    {
        $this->totalNumberOfAttributes->addToChannelAndLocale($channelCode, $localeCode, $number);

        return $this;
    }

    public function getTotalNumberOfAttributes(): ChannelLocaleDataCollection
    {
        return $this->totalNumberOfAttributes;
    }
}

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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class CompletenessCalculationResult
{
    /** @var ChannelLocaleRateCollection */
    private $rates;

    /** @var ChannelLocaleDataCollection */
    private $missingAttributes;

    public function __construct()
    {
        $this->rates = new ChannelLocaleRateCollection();
        $this->missingAttributes = new ChannelLocaleDataCollection();
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
}

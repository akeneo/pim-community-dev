<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\ValueObject\Rate;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleRateCollection
{
    private ChannelLocaleDataCollection $rates;

    public function __construct()
    {
        $this->rates = new ChannelLocaleDataCollection();
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

    public function toArrayLetter(): array
    {
        return $this->rates->mapWith(function (Rate $rate) {
            return $rate->toLetter();
        });
    }
}

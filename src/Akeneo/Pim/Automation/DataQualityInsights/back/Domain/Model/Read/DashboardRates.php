<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;

final class DashboardRates implements \JsonSerializable
{
    /** @var array */
    private $rates;

    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /** @var string */
    private $periodicity;

    public function __construct(array $rates, ChannelCode $channelCode, LocaleCode $localeCode, Periodicity $periodicity)
    {
        $this->rates = $rates;
        $this->channelCode = strval($channelCode);
        $this->localeCode = strval($localeCode);
        $this->periodicity = strval($periodicity);
    }

    public function jsonSerialize()
    {
        $result = [];

        foreach ($this->rates as $axisName => $projectionByPeriodicity) {
            $result[$axisName] = [];
            if (! array_key_exists($this->periodicity, $projectionByPeriodicity)) {
                continue;
            }

            foreach ($projectionByPeriodicity[$this->periodicity] as $day => $projectionByDay) {
                $result[$axisName][$day] = [];
                if (! isset($projectionByDay[$this->channelCode][$this->localeCode])) {
                    continue;
                }

                $result[$axisName][$day] = $projectionByDay[$this->channelCode][$this->localeCode];
            }
        }

        return $result;
    }
}

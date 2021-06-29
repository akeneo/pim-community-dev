<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleRankCollection
{
    /** @var ChannelLocaleDataCollection */
    private $ranks;

    public function __construct()
    {
        $this->ranks = new ChannelLocaleDataCollection();
    }

    public function addRank(ChannelCode $channelCode, LocaleCode $localeCode, Rank $rank): self
    {
        $this->ranks->addToChannelAndLocale($channelCode, $localeCode, $rank);

        return $this;
    }

    public static function fromNormalizedRanks(array $normalizedRanks, \Closure $getNormalizedRankValue): self
    {
        $rankCollection = new self();

        $rankCollection->ranks = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData(
            $normalizedRanks,
            function ($normalizedRank) use ($getNormalizedRankValue) {
                return Rank::fromInt(intval($getNormalizedRankValue($normalizedRank)));
            });

        return $rankCollection;
    }

    public function toArrayInt(): array
    {
        return $this->ranks->mapWith(function (Rank $rank) {
            return $rank->toInt();
        });
    }
}

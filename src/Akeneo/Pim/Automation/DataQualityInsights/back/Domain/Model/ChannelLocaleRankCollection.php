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
            fn($normalizedRank) => Rank::fromInt((int) $getNormalizedRankValue($normalizedRank)));

        return $rankCollection;
    }

    public function toArrayInt(): array
    {
        return $this->ranks->mapWith(fn(Rank $rank) => $rank->toInt());
    }
}

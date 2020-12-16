<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RanksDistribution
{
    /** @var array */
    private $ranksDistribution;

    private const DEFAULT_DISTRIBUTION = [
        'rank_1' => 0,
        'rank_2' => 0,
        'rank_3' => 0,
        'rank_4' => 0,
        'rank_5' => 0,
    ];

    public function __construct(array $ranksDistribution)
    {
        if (!empty(array_diff_key($ranksDistribution, self::DEFAULT_DISTRIBUTION))) {
            throw new \InvalidArgumentException('Invalid rank keys');
        }

        $this->ranksDistribution = array_replace(self::DEFAULT_DISTRIBUTION, array_map('intval', $ranksDistribution));
    }

    public function toArray(): array
    {
        return $this->ranksDistribution;
    }

    public function getPercentages(): array
    {
        $total = array_sum($this->ranksDistribution);

        return array_map(function ($distribution) use ($total) {
            return round($distribution / $total * 100, 2);
        }, $this->ranksDistribution);
    }

    public function getAverageRank(): ?Rank
    {
        $distributionSum = array_sum($this->ranksDistribution);

        if (0 === $distributionSum) {
            return null;
        }

        $total = 0;
        $ranks = [];
        foreach ($this->ranksDistribution as $rankCode => $distribution) {
            $rank = Rank::fromString($rankCode);
            $ranks[$rank->toInt()] = $rank;
            $total += $rank->toInt() * $distribution;
        }

        $average = intval(round($total / $distributionSum));

        return $ranks[$average];
    }
}

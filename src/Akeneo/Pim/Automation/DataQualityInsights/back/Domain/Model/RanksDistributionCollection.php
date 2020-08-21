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

/**
 * Expected format for the distribution of the ranks per axis/channel/locale:
 *  [
 *      "consistency" => [
 *          "mobile" => [
 *              "en_US" => [
 *                  "rank_1" => 25,
 *                  "rank_2" => 27,
 *                  "rank_3" => 36,
 *                  "rank_4" => 37,
 *                  "rank_5" => 36
 *              ]
 *          ],
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ]
 *          ]
 *      ],
 *      "enrichment" => [
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ]
 *          ]
 *      ]
 *  ];
 */
final class RanksDistributionCollection implements \IteratorAggregate
{
    private $axisChannelLocaleRanksDistributions;

    public function __construct(array $axisChannelLocaleRanksDistributions)
    {
        $this->axisChannelLocaleRanksDistributions = $this->mapRanksDistributions(function (array $ranksDistribution) {
            return new RanksDistribution($ranksDistribution);
        }, $axisChannelLocaleRanksDistributions);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->axisChannelLocaleRanksDistributions);
    }

    public function toArray(): array
    {
        return $this->mapRanksDistributions(function (RanksDistribution $ranksDistribution) {
            return $ranksDistribution->toArray();
        }, $this->axisChannelLocaleRanksDistributions);
    }

    public function getAverageRanks(): array
    {
        return $this->mapRanksDistributions(function (RanksDistribution $ranksDistribution) {
            return $ranksDistribution->getAverageRank();
        }, $this->axisChannelLocaleRanksDistributions);
    }

    private function mapRanksDistributions(callable $callback, array $axisChannelLocaleRanksDistributions): array
    {
        $mappedRanksDistributions = [];
        foreach ($axisChannelLocaleRanksDistributions as $axis => $channelLocaleRanksDistributions) {
            if (!is_array($channelLocaleRanksDistributions)) {
                throw new \InvalidArgumentException('the ranks distributions per channel are malformed');
            }
            foreach ($channelLocaleRanksDistributions as $channel => $localeRanksDistributions) {
                if (!is_array($localeRanksDistributions)) {
                    throw new \InvalidArgumentException('The ranks distributions per locale are malformed');
                }
                foreach ($localeRanksDistributions as $locale => $ranksDistribution) {
                    if (!is_array($ranksDistribution) && !$ranksDistribution instanceof RanksDistribution) {
                        throw new \InvalidArgumentException('The ranks distributions are malformed');
                    }
                    $mappedRanksDistributions[$axis][$channel][$locale] = $callback($ranksDistribution);
                }
            }
        }

        return $mappedRanksDistributions;
    }
}

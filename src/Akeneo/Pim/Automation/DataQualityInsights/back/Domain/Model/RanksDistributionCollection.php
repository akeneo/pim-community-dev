<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

/**
 * Expected format for the distribution of the ranks per channel/locale:
 *  [
 *          "mobile" => [
 *              "en_US" => [
 *                  "rank_1" => 25,
 *                  "rank_2" => 27,
 *                  "rank_3" => 36,
 *                  "rank_4" => 37,
 *                  "rank_5" => 36
 *              ],
 *          ],
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ],
 *          ],
 *          "ecommerce" => [
 *              "en_US" => [
 *                  "rank_1" => 33,
 *                  "rank_2" => 33,
 *                  "rank_3" => 28,
 *                  "rank_4" => 29,
 *                  "rank_5" => 38
 *              ],
 *          ],
 *  ];
 */
final class RanksDistributionCollection implements \IteratorAggregate
{
    private array $channelLocaleRanksDistributions;

    public function __construct(array $channelLocaleRanksDistributions)
    {
        $this->channelLocaleRanksDistributions = $this->mapRanksDistributions(function (array $ranksDistribution) {
            return new RanksDistribution($ranksDistribution);
        }, $channelLocaleRanksDistributions);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->channelLocaleRanksDistributions);
    }

    public function toArray(): array
    {
        return $this->mapRanksDistributions(function (RanksDistribution $ranksDistribution) {
            return $ranksDistribution->toArray();
        }, $this->channelLocaleRanksDistributions);
    }

    public function getAverageRanks(): array
    {
        return $this->mapRanksDistributions(function (RanksDistribution $ranksDistribution) {
            return $ranksDistribution->getAverageRank();
        }, $this->channelLocaleRanksDistributions);
    }

    private function mapRanksDistributions(callable $callback, array $channelLocaleRanksDistributions): array
    {
        $mappedRanksDistributions = [];
        foreach ($channelLocaleRanksDistributions as $channel => $localeRanksDistributions) {
            if (!is_array($localeRanksDistributions)) {
                throw new \InvalidArgumentException('The ranks distributions per locale are malformed');
            }
            foreach ($localeRanksDistributions as $locale => $ranksDistribution) {
                if (!is_array($ranksDistribution) && !$ranksDistribution instanceof RanksDistribution) {
                    throw new \InvalidArgumentException('The ranks distributions are malformed');
                }
                $mappedRanksDistributions[$channel][$locale] = $callback($ranksDistribution);
            }
        }

        return $mappedRanksDistributions;
    }
}

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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use PhpSpec\ObjectBehavior;

final class DashboardRatesProjectionSpec extends ObjectBehavior
{
    public function it_returns_the_ranks_distributions_for_a_common_day()
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();

        $this->beConstructedWith(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->getRanksDistributionsPerTimePeriod()->shouldBeLike([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ]
        ]);
    }

    public function it_returns_the_ranks_distributions_for_a_last_day_of_a_week()
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-19'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();

        $this->beConstructedWith(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->getRanksDistributionsPerTimePeriod()->shouldBeLike([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ],
            TimePeriod::WEEKLY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ]
        ]);
    }

    public function it_returns_the_ranks_distributions_for_a_last_day_of_a_month()
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-31'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();

        $this->beConstructedWith(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->getRanksDistributionsPerTimePeriod()->shouldBeLike([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ],
            TimePeriod::MONTHLY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ]
        ]);
    }

    public function it_returns_the_ranks_distributions_for_a_last_day_of_a_year()
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-31'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();

        $this->beConstructedWith(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->getRanksDistributionsPerTimePeriod()->shouldBeLike([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ],
            TimePeriod::MONTHLY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ],
            TimePeriod::YEARLY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray(),
            ]
        ]);
    }

    private function buildRandomRanksDistributionCollection(): RanksDistributionCollection
    {
        return new RanksDistributionCollection([
            "ecommerce" => [
                "en_US" => [
                    "rank_1" => rand(1, 100),
                    "rank_2" => rand(1, 100),
                    "rank_3" => rand(1, 100),
                    "rank_4" => rand(1, 100),
                    "rank_5" => rand(1, 100)
                ],
            ],
        ]);
    }
}

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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\StatisticsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;

final class GetAskFranklinCreditsHandler
{
    /** @var StatisticsProviderInterface */
    private $statisticsProvider;

    public function __construct(StatisticsProviderInterface $statisticsProvider)
    {
        $this->statisticsProvider = $statisticsProvider;
    }

    public function handle(GetAskFranklinCreditsQuery $query): KeyFigureCollection
    {
        $statistics = $this->statisticsProvider->getCreditsUsageStatistics();

        return new KeyFigureCollection(
            [
                new KeyFigure('credits_consumed', $statistics->getConsumed()),
                new KeyFigure('credits_left', $statistics->getLeft()),
                new KeyFigure('credits_total', $statistics->getTotal()),
            ]
        );
    }
}

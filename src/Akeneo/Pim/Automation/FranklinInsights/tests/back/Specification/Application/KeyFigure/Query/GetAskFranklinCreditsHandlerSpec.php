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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\StatisticsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetAskFranklinCreditsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetAskFranklinCreditsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics;
use PhpSpec\ObjectBehavior;

class GetAskFranklinCreditsHandlerSpec extends ObjectBehavior
{
    public function let(
        StatisticsProviderInterface $statisticsProvider
    ): void {
        $this->beConstructedWith($statisticsProvider);
    }

    public function it_is_a_get_ask_fraklin_credits_handler(): void
    {
        $this->shouldHaveType(GetAskFranklinCreditsHandler::class);
    }

    public function it_handles_a_get_ask_franklin_credits_query(
        $statisticsProvider
    ): void {
        $statisticsProvider->getCreditsUsageStatistics()->willReturn(
            new CreditsUsageStatistics(
                [
                    'consumed' => 2,
                    'left' => 1,
                    'total' => 3,
                ]
            )
        );

        $query = new GetAskFranklinCreditsQuery();
        $result = $this->handle($query);

        $result->shouldReturnAnInstanceOf(KeyFigureCollection::class);
        $result->shouldIterateLike(
            new KeyFigureCollection(
                [
                    new KeyFigure(
                        'credits_consumed',
                        2
                    ),
                    new KeyFigure(
                        'credits_left',
                        1
                    ),
                    new KeyFigure(
                        'credits_total',
                        3
                    ),
                ]
            )
        );
    }
}

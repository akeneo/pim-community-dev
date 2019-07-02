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
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetCreditsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetCreditsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\CreditsUsageStatistics;
use PhpSpec\ObjectBehavior;

class GetCreditsHandlerSpec extends ObjectBehavior
{
    public function let(
        StatisticsProviderInterface $statisticsProvider
    ): void {
        $this->beConstructedWith($statisticsProvider);
    }

    public function it_is_a_get_ask_fraklin_credits_handler(): void
    {
        $this->shouldHaveType(GetCreditsHandler::class);
    }

    public function it_handles_a_get_ask_franklin_credits_query($statisticsProvider): void {
        $creditsUsageStats = new CreditsUsageStatistics(2, 1, 3);
        $statisticsProvider->getCreditsUsageStatistics()->willReturn($creditsUsageStats);

        $query = new GetCreditsQuery();
        $result = $this->handle($query);

        $result->shouldReturn($creditsUsageStats);
    }
}

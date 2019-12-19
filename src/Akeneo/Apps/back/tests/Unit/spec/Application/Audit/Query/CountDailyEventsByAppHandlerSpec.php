<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Audit\Query;

use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByAppHandler;
use Akeneo\Apps\Application\Audit\Query\CountDailyEventsByAppQuery;
use Akeneo\Apps\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
use Akeneo\Apps\Domain\Audit\Persistence\Query\SelectAppsEventCountByDayQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppHandlerSpec extends ObjectBehavior
{
    function let(SelectAppsEventCountByDayQuery $selectAppsEventCountByDayQuery)
    {
        $this->beConstructedWith($selectAppsEventCountByDayQuery);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CountDailyEventsByAppHandler::class);
    }

    function it_handles_the_event_count($selectAppsEventCountByDayQuery)
    {
        $eventCountByApp1 = new WeeklyEventCounts('Magento');
        $eventCountByApp1->addDailyEventCount(new DailyEventCount(42, new \DateTime('2019-12-10')));
        $eventCountByApp1->addDailyEventCount(new DailyEventCount(123, new \DateTime('2019-12-11')));

        $eventCountByApp2 = new WeeklyEventCounts('Bynder');
        $eventCountByApp2->addDailyEventCount(new DailyEventCount(36, new \DateTime('2019-12-11')));

        $selectAppsEventCountByDayQuery
            ->execute('product_created', '2019-12-10', '2019-12-12')
            ->willReturn([$eventCountByApp1, $eventCountByApp2]);

        $query = new CountDailyEventsByAppQuery('product_created', '2019-12-10', '2019-12-12');
        $this->handle($query)->shouldReturn([$eventCountByApp1, $eventCountByApp2]);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionHandlerSpec extends ObjectBehavior
{
    function let(SelectConnectionsEventCountByDayQuery $selectConnectionsEventCountByDayQuery)
    {
        $this->beConstructedWith($selectConnectionsEventCountByDayQuery);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CountDailyEventsByConnectionHandler::class);
    }

    function it_handles_the_event_count($selectConnectionsEventCountByDayQuery)
    {
        $eventCountByConnection1 = new WeeklyEventCounts(
            'Magento',
            '2019-12-09',
            '2019-12-17',
            []
        );

        $eventCountByConnection2 = new WeeklyEventCounts(
            'Bynder',
            '2019-12-09',
            '2019-12-17',
            []
        );

        $selectConnectionsEventCountByDayQuery
            ->execute(EventTypes::PRODUCT_CREATED, '2019-12-09', '2019-12-17')
            ->willReturn([$eventCountByConnection1, $eventCountByConnection2]);

        $query = new CountDailyEventsByConnectionQuery(EventTypes::PRODUCT_CREATED, '2019-12-09', '2019-12-17');
        $this->handle($query)->shouldReturn([$eventCountByConnection1, $eventCountByConnection2]);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
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
        $connectionsEventCounts = [
            AllConnectionCode::CODE => [
                [new \DateTimeImmutable('2020-01-01 12:00:00', new \DateTimeZone('UTC')), 3],
            ],
            'sap' => [
                [new \DateTimeImmutable('2020-01-01 12:00:00', new \DateTimeZone('UTC')), 1],
            ],
            'bynder' => [
                [new \DateTimeImmutable('2020-01-01 12:00:00', new \DateTimeZone('UTC')), 2],
            ]
        ];
        $selectConnectionsEventCountByDayQuery
            ->execute(
                EventTypes::PRODUCT_CREATED,
                new \DateTimeImmutable('2019-12-31 23:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-02 23:00:00', new \DateTimeZone('UTC')),
            )->willReturn($connectionsEventCounts);

        $expectedResult = [
            new WeeklyEventCounts(AllConnectionCode::CODE, '2020-01-01', '2020-01-02', 'Europe/Paris', $connectionsEventCounts[AllConnectionCode::CODE]),
            new WeeklyEventCounts('sap', '2020-01-01', '2020-01-02', 'Europe/Paris', $connectionsEventCounts['sap']),
            new WeeklyEventCounts('bynder', '2020-01-01', '2020-01-02', 'Europe/Paris', $connectionsEventCounts['bynder']),
        ];

        $query = new CountDailyEventsByConnectionQuery(
            EventTypes::PRODUCT_CREATED,
            '2020-01-01',
            '2020-01-02',
            'Europe/Paris'
        );
        $this->handle($query)->shouldIterateLike($expectedResult);
    }
}

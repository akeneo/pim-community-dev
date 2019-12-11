<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Application\Query;

use Akeneo\Apps\Audit\Application\Query\CountDailyEventsByAppHandler;
use Akeneo\Apps\Audit\Application\Query\CountDailyEventsByAppQuery;
use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByApp;
use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByDate;
use Akeneo\Apps\Audit\Domain\Persistence\Query\SelectAppsEventCountByDateQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppHandlerSpec extends ObjectBehavior
{
    function let(SelectAppsEventCountByDateQuery $selectAppsEventCountByDateQuery)
    {
        $this->beConstructedWith($selectAppsEventCountByDateQuery);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CountDailyEventsByAppHandler::class);
    }

    function it_handles_the_event_count($selectAppsEventCountByDateQuery)
    {
        $eventCountByApp1 = new EventCountByApp('Magento');
        $eventCountByApp1->addEventCount(new EventCountByDate(42, new \DateTime('2019-12-10')));
        $eventCountByApp1->addEventCount(new EventCountByDate(123, new \DateTime('2019-12-11')));

        $eventCountByApp2 = new EventCountByApp('Bynder');
        $eventCountByApp2->addEventCount(new EventCountByDate(36, new \DateTime('2019-12-11')));

        $selectAppsEventCountByDateQuery
            ->execute('product_created', '2019-12-10', '2019-12-12')
            ->willReturn([$eventCountByApp1, $eventCountByApp2]);

        $query = new CountDailyEventsByAppQuery('product_created', '2019-12-10', '2019-12-12');
        $this->handle($query)->shouldReturn([$eventCountByApp1, $eventCountByApp2]);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Domain\Model\Read;

use Akeneo\Apps\Audit\Application\Query\FetchAppsEventCountByEventHandler;
use Akeneo\Apps\Audit\Application\Query\FetchAppsEventCountByEventQuery;
use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByApp;
use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByDate;
use Akeneo\Apps\Audit\Domain\Persistence\Query\SelectAppsEventCountByDateQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppsEventCountByEventHandlerSpec extends ObjectBehavior
{
    function let(SelectAppsEventCountByDateQuery $selectAppsEventCountByDateQuery)
    {
        $this->beConstructedWith($selectAppsEventCountByDateQuery);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(FetchAppsEventCountByEventHandler::class);
    }

    function it_handles_the_event_count($selectAppsEventCountByDateQuery)
    {
        $eventCountByApp = new EventCountByApp('Magento');
        $eventCountByApp->addEventCount(new EventCountByDate(42, new \DateTime('2019-12-10')));

        $selectAppsEventCountByDateQuery
            ->execute('product_created', '2019-12-10', '2019-12-12')
            ->willReturn([$eventCountByApp]);

        $expectedData = [
            'Magento' => [
                '2019-12-10' => 42,
                '2019-12-11' => 123,
            ],
            'Bynder' => [
                '2019-12-11' => 36,
            ]
        ];
        $query = new FetchAppsEventCountByEventQuery('product_created', '2019-12-12', '2019-12-14');
        $this->handle($query)->shouldReturn($expectedData);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionQuerySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(EventTypes::PRODUCT_CREATED, '2019-12-10', '2019-12-12');
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CountDailyEventsByConnectionQuery::class);
    }

    function it_returns_the_event_type()
    {
        $this->eventType()->shouldReturn(EventTypes::PRODUCT_CREATED);
    }

    function it_returns_the_start_date()
    {
        $this->startDate()->shouldReturn('2019-12-10');
    }

    function it_returns_the_end_date()
    {
        $this->endDate()->shouldReturn('2019-12-12');
    }
}

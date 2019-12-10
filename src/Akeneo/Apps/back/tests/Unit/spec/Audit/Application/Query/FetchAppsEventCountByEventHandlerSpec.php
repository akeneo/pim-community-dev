<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Domain\Model\Read;

use Akeneo\Apps\Audit\Application\Query\FetchAppsEventCountByEventQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppsEventCountByEventHandlerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('product_created', '2019-12-10', '2019-12-12');
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(FetchAppsEventCountByEventQuery::class);
    }

    function it_returns_the_event_type()
    {
        $this->eventType()->shouldReturn('product_created');
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

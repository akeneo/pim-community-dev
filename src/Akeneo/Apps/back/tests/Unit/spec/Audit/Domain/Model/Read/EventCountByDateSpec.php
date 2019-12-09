<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Domain\Model\Read;

use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByDate;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventCountByDateSpec extends ObjectBehavior
{
    function let()
    {
        $eventDate = new \DateTime('2019-12-03', new \DateTimeZone('UTC'));
        $this->beConstructedWith(5, $eventDate);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EventCountByDate::class);
    }

    function it_returns_the_event_date()
    {
        $eventDate = $eventDate = new \DateTime('2019-12-14', new \DateTimeZone('UTC'));
        $this->beConstructedWith(4, $eventDate);

        $this->date()->shouldReturn($eventDate);
    }

    function it_returns_the_event_count()
    {
        $eventDate = $eventDate = new \DateTime('2019-12-09', new \DateTimeZone('UTC'));
        $this->beConstructedWith(143, $eventDate);

        $this->count()->shouldReturn(143);
    }
}

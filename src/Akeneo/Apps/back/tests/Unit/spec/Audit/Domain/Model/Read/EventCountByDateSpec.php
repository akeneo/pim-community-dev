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

    function it_normalizes_an_app()
    {
        $this->normalize()->shouldReturn([
            'event_count' => 5,
            'event_date' => '2019-12-03',
        ]);
    }
}

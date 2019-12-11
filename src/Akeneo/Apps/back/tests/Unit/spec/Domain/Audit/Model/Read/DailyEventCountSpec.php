<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Audit\Model\Read;

use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DailyEventCountSpec extends ObjectBehavior
{
    function let()
    {
        $eventDate = new \DateTime('2019-12-03', new \DateTimeZone('UTC'));
        $this->beConstructedWith(5, $eventDate);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(DailyEventCount::class);
    }

    function it_normalizes_the_event_count()
    {
        $this->normalize()->shouldReturn([
            'date' => '2019-12-03',
            'value' => 5
        ]);
    }
}

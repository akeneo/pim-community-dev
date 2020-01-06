<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\DailyEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DailyEventCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $eventDate = new \DateTime('2019-12-03', new \DateTimeZone('UTC'));
        $this->beConstructedWith(5, $eventDate);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(DailyEventCount::class);
    }

    public function it_normalizes_the_event_count(): void
    {
        $this->normalize()->shouldReturn(
            [
                '2019-12-03' => 5,
            ]
        );
    }
}

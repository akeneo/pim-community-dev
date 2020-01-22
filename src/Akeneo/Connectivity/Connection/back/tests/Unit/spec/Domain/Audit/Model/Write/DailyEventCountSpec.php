<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\DailyEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.holly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DailyEventCountSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'magento',
            '2019-12-30',
            329,
            EventTypes::PRODUCT_CREATED
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DailyEventCount::class);
    }

    public function it_returns_the_connection_code()
    {
        $this->connectionCode()->shouldBe('magento');
    }

    public function it_returns_the_event_date()
    {
        $this->eventDate()->shouldBe('2019-12-30');
    }

    public function it_returns_the_event_count()
    {
        $this->eventCount()->shouldBe(329);
    }

    public function it_returns_the_event_type()
    {
        $this->eventType()->shouldBe('product_created');
    }
}

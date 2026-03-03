<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.holly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyEventCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'magento',
            HourlyInterval::createFromDateTime(
                new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
            ),
            329,
            EventTypes::PRODUCT_CREATED
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(HourlyEventCount::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->connectionCode()->shouldBe('magento');
    }

    public function it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith(
            'magento',
            $hourlyInterval,
            329,
            EventTypes::PRODUCT_CREATED
        );

        $this->hourlyInterval()->shouldBe($hourlyInterval);
    }

    public function it_returns_the_event_count(): void
    {
        $this->eventCount()->shouldBe(329);
    }

    public function it_returns_the_event_type(): void
    {
        $this->eventType()->shouldBe(EventTypes::PRODUCT_CREATED);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PeriodEventCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'magento',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
            [
                new HourlyEventCount(
                    new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                    1
                )
            ]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(PeriodEventCount::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('magento');
    }

    public function it_returns_the_from_date_time(): void
    {
        $this->fromDateTime()->shouldBeLike(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_returns_the_up_to_date_time(): void
    {
        $this->upToDateTime()->shouldBeLike(new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')));
    }

    public function it_returns_the_hourly_event_counts(): void
    {
        $this->hourlyEventCounts()->shouldBeLike([
            new HourlyEventCount(
                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                1
            )
        ]);
    }
}

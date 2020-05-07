<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'SAP',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))),
            104
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(UpdateDataDestinationProductEventCountCommand::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('SAP');
    }

    public function it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith('SAP', $hourlyInterval, 102);

        $this->hourlyInterval()->shouldReturn($hourlyInterval);
    }

    public function it_returns_the_product_event_count(): void
    {
        $this->productEventCount()->shouldReturn(104);
    }
}

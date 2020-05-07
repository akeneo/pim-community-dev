<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountCommand;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataSourceProductEventCountCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        ));
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(UpdateDataSourceProductEventCountCommand::class);
    }

    public function it_returns_the_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(
            new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $this->beConstructedWith($hourlyInterval);

        $this->hourlyInterval()->shouldReturn($hourlyInterval);
    }
}

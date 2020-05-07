<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use PhpSpec\ObjectBehavior;

class UpdateConnectionErrorCountCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(new \DateTime('now'));
        $this->beConstructedWith('erp', $hourlyInterval, 1618, ErrorTypes::BUSINESS);
    }

    public function it_is_an_update_connection_error_count_command(): void
    {
        $this->shouldHaveType(UpdateConnectionErrorCountCommand::class);
    }

    public function it_provides_a_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('erp');
    }

    public function it_provides_an_error_count(): void
    {
        $this->errorCount()->shouldReturn(1618);
    }

    public function it_provides_an_error_type(): void
    {
        $this->errorType()->shouldReturn(ErrorTypes::BUSINESS);
    }

    public function it_provides_a_hourly_interval(): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(new \DateTime('now'));
        $this->beConstructedWith('erp', $hourlyInterval, 1618, ErrorTypes::BUSINESS);

        $this->hourlyInterval()->shouldReturn($hourlyInterval);
    }
}

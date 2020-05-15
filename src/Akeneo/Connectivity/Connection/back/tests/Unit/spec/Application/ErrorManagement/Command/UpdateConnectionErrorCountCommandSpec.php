<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use PhpSpec\ObjectBehavior;

class UpdateConnectionErrorCountCommandSpec extends ObjectBehavior
{
    public function let(HourlyErrorCount $firstCount, HourlyErrorCount $secondCount): void
    {
        $this->beConstructedWith([$firstCount, $secondCount]);
    }

    public function it_is_an_update_connection_error_count_command(): void
    {
        $this->shouldHaveType(UpdateConnectionErrorCountCommand::class);
    }

    public function it_provides_error_counts($firstCount, $secondCount): void
    {
        $this->errorCounts()->shouldReturn([$firstCount, $secondCount]);
    }
}

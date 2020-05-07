<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateConnectionErrorCountHandlerSpec extends ObjectBehavior
{
    public function let(ErrorCountRepository $errorCountRepository): void
    {
        $this->beConstructedWith($errorCountRepository);
    }

    public function it_is_an_update_connection_error_count_handler(): void
    {
        $this->shouldHaveType(UpdateConnectionErrorCountHandler::class);
    }

    public function it_updates_an_error_count($errorCountRepository): void
    {
        $hourlyInterval = HourlyInterval::createFromDateTime(new \DateTime('now'));
        $command = new UpdateConnectionErrorCountCommand(
            'erp',
            $hourlyInterval,
            42,
            ErrorTypes::BUSINESS
        );

        $errorCountRepository->upsert(Argument::type(HourlyErrorCount::class))->shouldBeCalled();

        $this->handle($command);
    }
}

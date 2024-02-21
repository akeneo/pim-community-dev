<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PhpSpec\ObjectBehavior;

class UpdateConnectionErrorCountHandlerSpec extends ObjectBehavior
{
    public function let(ErrorCountRepositoryInterface $errorCountRepository): void
    {
        $this->beConstructedWith($errorCountRepository);
    }

    public function it_is_an_update_connection_error_count_handler(): void
    {
        $this->shouldHaveType(UpdateConnectionErrorCountHandler::class);
    }

    public function it_updates_error_counts($errorCountRepository): void
    {
        $firstCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::BUSINESS
        );
        $secondCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::TECHNICAL
        );
        $command = new UpdateConnectionErrorCountCommand([$firstCount, $secondCount]);

        $errorCountRepository->upsert($firstCount)->shouldBeCalled();
        $errorCountRepository->upsert($secondCount)->shouldBeCalled();

        $this->handle($command);
    }

    public function it_does_not_update_a_0_count($errorCountRepository): void
    {
        $firstCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            2,
            ErrorTypes::BUSINESS
        );
        $secondCount = new HourlyErrorCount(
            'erp',
            HourlyInterval::createFromDateTime(new \DateTime('now')),
            0,
            ErrorTypes::TECHNICAL
        );
        $command = new UpdateConnectionErrorCountCommand([$firstCount, $secondCount]);

        $errorCountRepository->upsert($firstCount)->shouldBeCalled();
        $errorCountRepository->upsert($secondCount)->shouldNotBeCalled();

        $this->handle($command);
    }
}

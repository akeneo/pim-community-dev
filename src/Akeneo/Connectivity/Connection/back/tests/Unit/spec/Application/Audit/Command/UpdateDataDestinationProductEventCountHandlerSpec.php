<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\UpsertEventCountQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountHandlerSpec extends ObjectBehavior
{
    public function let(UpsertEventCountQueryInterface $upsertEventCountQuery): void
    {
        $this->beConstructedWith($upsertEventCountQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(UpdateDataDestinationProductEventCountHandler::class);
    }

    public function it_saves_data_destination_product_event_count(UpsertEventCountQueryInterface $upsertEventCountQuery): void
    {
        $command = new UpdateDataDestinationProductEventCountCommand(
            'ecommerce',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
            3
        );

        $hourlyEventCount = new HourlyEventCount(
            $command->connectionCode(),
            $command->hourlyInterval(),
            $command->productEventCount(),
            EventTypes::PRODUCT_READ
        );

        $upsertEventCountQuery->execute($hourlyEventCount)->shouldBeCalled();

        $this->handle($command);
    }
}

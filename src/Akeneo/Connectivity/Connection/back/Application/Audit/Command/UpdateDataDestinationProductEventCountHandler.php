<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepository;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountHandler
{
    private EventCountRepository $eventCountRepository;

    public function __construct(EventCountRepository $eventCountRepository)
    {
        $this->eventCountRepository = $eventCountRepository;
    }

    public function handle(UpdateDataDestinationProductEventCountCommand $command): void
    {
        $hourlyEventCount = new HourlyEventCount(
            $command->connectionCode(),
            $command->hourlyInterval(),
            $command->productEventCount(),
            EventTypes::PRODUCT_READ
        );

        $this->eventCountRepository->upsert($hourlyEventCount);
    }
}

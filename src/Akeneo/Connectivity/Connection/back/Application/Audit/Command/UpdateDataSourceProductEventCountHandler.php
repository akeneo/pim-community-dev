<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateDataSourceProductEventCountHandler
{
    private ExtractConnectionsProductEventCountQueryInterface $extractConnectionsEventCountQuery;

    private EventCountRepositoryInterface $eventCountRepository;

    public function __construct(
        ExtractConnectionsProductEventCountQueryInterface $extractConnectionsEventCountQuery,
        EventCountRepositoryInterface $eventCountRepository
    ) {
        $this->extractConnectionsEventCountQuery = $extractConnectionsEventCountQuery;
        $this->eventCountRepository = $eventCountRepository;
    }

    public function handle(UpdateDataSourceProductEventCountCommand $command): void
    {
        $createdProductsCount = $this->extractConnectionsEventCountQuery
            ->extractCreatedProductsByConnection($command->hourlyInterval());
        $this->eventCountRepository->bulkInsert($createdProductsCount);

        $updatedProductsCount = $this->extractConnectionsEventCountQuery
            ->extractUpdatedProductsByConnection($command->hourlyInterval());
        $this->eventCountRepository->bulkInsert($updatedProductsCount);
    }
}

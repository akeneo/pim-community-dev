<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountHandler
{
    /** @var ExtractConnectionsProductEventCountQuery */
    private $extractConnectionsEventCountQuery;

    /** @var EventCountRepository */
    private $eventCountRepository;

    public function __construct(ExtractConnectionsProductEventCountQuery $extractConnectionsEventCountQuery, EventCountRepository $eventCountRepository)
    {
        $this->extractConnectionsEventCountQuery = $extractConnectionsEventCountQuery;
        $this->eventCountRepository = $eventCountRepository;
    }

    public function handle(UpdateProductEventCountCommand $command): void
    {
        // TODO: Use Read Models and transform into write models?

        $createdProductsCount = $this->extractConnectionsEventCountQuery->extractCreatedProductsByConnection($command->eventDate());
        $this->eventCountRepository->bulkInsert($createdProductsCount);
        $createdProductsAllCount = $this->extractConnectionsEventCountQuery->extractAllCreatedProducts($command->eventDate());
        $this->eventCountRepository->bulkInsert($createdProductsAllCount);

        $updatedProductsCount = $this->extractConnectionsEventCountQuery->extractUpdatedProductsByConnection($command->eventDate());
        $this->eventCountRepository->bulkInsert($updatedProductsCount);
        $updatedProductsAllCount = $this->extractConnectionsEventCountQuery->extractAllUpdatedProducts($command->eventDate());
        $this->eventCountRepository->bulkInsert($updatedProductsAllCount);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\BulkInsertEventCountsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\ExtractConnectionsProductEventCountQueryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateDataSourceProductEventCountHandler
{
    public function __construct(
        private ExtractConnectionsProductEventCountQueryInterface $extractConnectionsEventCountQuery,
        private BulkInsertEventCountsQueryInterface $bulkInsertEventCountsQuery,
    ) {
    }

    public function handle(UpdateDataSourceProductEventCountCommand $command): void
    {
        $createdProductsCount = $this->extractConnectionsEventCountQuery
            ->extractCreatedProductsByConnection($command->hourlyInterval());
        $this->bulkInsertEventCountsQuery->execute($createdProductsCount);

        $updatedProductsCount = $this->extractConnectionsEventCountQuery
            ->extractUpdatedProductsByConnection($command->hourlyInterval());
        $this->bulkInsertEventCountsQuery->execute($updatedProductsCount);
    }
}

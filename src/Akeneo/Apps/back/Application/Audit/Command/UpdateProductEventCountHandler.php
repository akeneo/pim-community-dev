<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Command;

use Akeneo\Apps\Domain\Audit\Persistence\Query\ExtractAppsProductEventCountQuery;
use Akeneo\Apps\Domain\Audit\Persistence\Repository\EventCountRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountHandler
{
    /** @var ExtractAppsProductEventCountQuery */
    private $extractAppsEventCountQuery;
    /** @var EventCountRepository */
    private $eventCountRepository;

    public function __construct(ExtractAppsProductEventCountQuery $extractAppsEventCountQuery, EventCountRepository $eventCountRepository)
    {
        $this->extractAppsEventCountQuery = $extractAppsEventCountQuery;
        $this->eventCountRepository = $eventCountRepository;
    }

    public function handle(): void
    {
        // 2. Extract events query

        // 3. Transform into write models?

        // 4. Insert audit data


        $createdProductsCount = $this->extractAppsEventCountQuery->extractCreatedProducts($date);
        $this->eventCountRepository->bulkInsert($createdProductsCount);

        $updatedProductsCount = $this->extractAppsEventCountQuery->extractUpdatedProducts($date);
        $this->eventCountRepository->bulkInsert($updatedProductsCount);
    }
}

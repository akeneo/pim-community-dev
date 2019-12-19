<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Service;

use Akeneo\Apps\Domain\Audit\Persistence\Query\ExtractAppsEventCountQuery;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountService
{
    /** @var ExtractAppsEventCountQuery */
    private $extractAppsEventCountQuery;

    public function __construct(ExtractAppsEventCountQuery $extractAppsEventCountQuery)
    {
        $this->extractAppsEventCountQuery = $extractAppsEventCountQuery;
    }

    public function execute(): void
    {
        // 1. List app source with user

        // 2. Extract events query
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->extractAppsEventCountQuery->extractCreatedProducts($datetime);
        $this->extractAppsEventCountQuery->extractUpdatedProducts();

        // 3. Transform into write models?

        // 4. Insert audit data

    }
}

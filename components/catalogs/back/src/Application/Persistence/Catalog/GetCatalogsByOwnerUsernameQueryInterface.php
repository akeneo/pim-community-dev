<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCatalogsByOwnerUsernameQueryInterface
{
    /**
     * @return array<Catalog>
     */
    public function execute(string $ownerUsername, int $offset = 0, int $limit = 100): array;
}

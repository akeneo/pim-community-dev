<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductUuidsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandler
{
    public function __construct(
        private GetProductUuidsQueryInterface $query,
    ) {
    }

    /**
     * @return array<string>
     */
    public function __invoke(GetProductUuidsQuery $query): array
    {
        return $this->query->execute(
            $query->getCatalogId(),
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );
    }
}

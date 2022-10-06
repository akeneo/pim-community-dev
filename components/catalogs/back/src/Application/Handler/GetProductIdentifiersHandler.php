<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductIdentifiersQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductIdentifiersQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifiersHandler
{
    public function __construct(
        private GetProductIdentifiersQueryInterface $query,
    ) {
    }

    /**
     * @return array<string>
     */
    public function __invoke(GetProductIdentifiersQuery $query): array
    {
        return $this->query->execute(
            $query->getCatalog(),
            $query->getSearchAfter(),
            $query->getLimit(),
        );
    }
}

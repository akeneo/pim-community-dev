<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductIdentifiersQueryInterface;
use Akeneo\Catalogs\Application\Service\DisableOnlyInvalidCatalogInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\InvalidCatalogException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductIdentifiersQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifiersHandler
{
    public function __construct(
        private GetProductIdentifiersQueryInterface $query,
        private DisableOnlyInvalidCatalogInterface $disableOnlyInvalidCatalog,
    ) {
    }

    /**
     * @return array<string>
     */
    public function __invoke(GetProductIdentifiersQuery $query): array
    {
        try {
            return $this->query->execute(
                $query->getCatalogId(),
                $query->getSearchAfter(),
                $query->getLimit(),
            );
        } catch (\Exception $exception) {
            $this->disableOnlyInvalidCatalog->disable($query->getCatalogId());
            throw new InvalidCatalogException(previous: $exception);
        }
    }
}

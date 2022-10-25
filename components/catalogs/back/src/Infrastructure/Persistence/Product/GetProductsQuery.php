<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Product\GetProductsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsQueryInterface
 */
class GetProductsQuery implements GetProductsQueryInterface
{
    public function __construct(
        private GetProductUuidsQueryInterface $getProductUuidsQuery,
        private GetConnectorProducts $getConnectorProducts,
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
        private GetUserIdFromUsernameQueryInterface $getUserIdFromUsernameQuery,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $uuids = $this->getProductUuidsQuery->execute($catalog, $searchAfter, $limit, $updatedAfter, $updatedBefore);

        $connectorProducts = $this->getConnectorProducts->fromProductUuids(
            \array_map(static fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids),
            $this->getUserIdFromUsernameQuery->execute($catalog->getOwnerUsername()),
            null,
            null,
            null,
        );

        /** @var array<Product> $products */
        return $this->connectorProductWithUuidNormalizer->normalizeConnectorProductList($connectorProducts);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductValueFiltersQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProductValueFilters from GetCatalogProductValueFiltersQueryInterface
 */
final class GetCatalogProductValueFiltersQuery implements GetCatalogProductValueFiltersQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $id): array
    {
        $query = <<<SQL
        SELECT
            catalog.product_value_filters
        FROM akeneo_catalog catalog
        WHERE catalog.id = :id
        SQL;

        /** @var string|false $result */
        $result = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        if (!$result) {
            throw new \LogicException('Catalog not found');
        }

        /** @var ProductValueFilters|null $filters */
        $filters = \json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($filters)) {
            throw new \LogicException('Invalid JSON in product_value_filters column');
        }

        return $filters;
    }
}

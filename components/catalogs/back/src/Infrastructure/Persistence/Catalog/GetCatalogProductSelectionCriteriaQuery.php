<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductSelectionCriteriaQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogProductSelectionCriteriaQuery implements GetCatalogProductSelectionCriteriaQueryInterface
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
            catalog.product_selection_criteria
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

        /** @var array<array-key, array{field: string, operator: string, value?: mixed, scope?: string|null, locale?: string|null}>|null $criteria */
        $criteria = \json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($criteria)) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        return $criteria;
    }
}

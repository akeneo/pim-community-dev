<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\FindCatalogProductSelectionCriteriaQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindCatalogProductSelectionCriteriaQuery implements FindCatalogProductSelectionCriteriaQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $id): ?array
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
            return null;
        }

        /** @var array<array-key,array{field: string, operator: string, value?: mixed}>|null $criteria */
        $criteria = \json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($criteria)) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        return $criteria;
    }
}

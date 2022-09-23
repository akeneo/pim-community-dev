<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogProductSelectionCriteriaQuery implements UpdateCatalogProductSelectionCriteriaQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $id, array $productSelectionCriteria): void
    {
        $query = <<<SQL
        UPDATE akeneo_catalog catalog
        SET product_selection_criteria = :product_selection_criteria
        WHERE catalog.id = :id
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'product_selection_criteria' => \array_values($productSelectionCriteria),
            ],
            [
                'product_selection_criteria' => Types::JSON,
            ]
        );
    }
}

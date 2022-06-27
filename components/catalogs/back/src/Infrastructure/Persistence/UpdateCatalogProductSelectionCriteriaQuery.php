<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

class UpdateCatalogProductSelectionCriteriaQuery implements UpdateCatalogProductSelectionCriteriaQueryInterface
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
                'product_selection_criteria' => $productSelectionCriteria,
            ],
            [
                'product_selection_criteria' => Types::JSON,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UpdateCatalogProductSelectionCriteriaQuery implements UpdateCatalogProductSelectionCriteriaQueryInterface
{
    public function __construct(
        private Connection $connection,
        private NormalizerInterface $normalizer,
    ) {
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
                'product_selection_criteria' => $this->normalizer->normalize($productSelectionCriteria, 'pqb'),
            ],
            [
                'product_selection_criteria' => Types::JSON,
            ]
        );
    }
}

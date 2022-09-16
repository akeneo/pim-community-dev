<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertCategoriesInSelectionIndexQuery
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $id, array $productSelectionCriteria): void
    {
        $query = <<<SQL
        UPDATE akeneo_catalog catalog
        SET categories_in_product_selection_criteria = :categories
        WHERE catalog.id = :id
        SQL;

        $categories = [];
        foreach ($productSelectionCriteria as $criterion) {
            if ($criterion['field'] === 'category') {
                $categories[] = $criterion['value'];
            }
        }

        $this->connection->executeQuery(
            $query,
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'categories' => \array_values(array_unique($categories)),
            ],
            [
                'categories' => Types::JSON,
            ]
        );
    }
}

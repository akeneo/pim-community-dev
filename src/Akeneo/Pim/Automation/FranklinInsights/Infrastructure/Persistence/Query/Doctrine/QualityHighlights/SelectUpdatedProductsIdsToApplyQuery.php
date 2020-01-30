<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectUpdatedProductsIdsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Doctrine\DBAL\Connection;

class SelectUpdatedProductsIdsToApplyQuery implements SelectUpdatedProductsIdsToApplyQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(Lock $lock, BatchSize $limit): array
    {
        $query = <<<SQL
        SELECT updated_products.entity_id
        FROM pimee_franklin_insights_quality_highlights_pending_items AS updated_products
        INNER JOIN pim_catalog_product AS product ON product.id = updated_products.entity_id
        LEFT JOIN pim_catalog_family family ON family.id = product.family_id
        LEFT JOIN pimee_franklin_insights_quality_highlights_pending_items AS updated_family
            ON updated_family.entity_type = :family_entity_type
            AND updated_family.entity_id = family.code
        WHERE updated_products.`action` = :update_action
            AND updated_products.entity_type = :product_entity_type
            AND updated_products.lock_id = :lock
            AND updated_family.entity_id IS NULL
        LIMIT :limit
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'update_action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
                'product_entity_type' => PendingItemsRepository::ENTITY_TYPE_PRODUCT,
                'family_entity_type' => PendingItemsRepository::ENTITY_TYPE_FAMILY,
                'lock' => $lock->__toString(),
                'limit' => $limit->toInt(),
            ],
            [
                'limit' => \PDO::PARAM_INT,
            ]
        );

        return array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN, 0));
    }
}

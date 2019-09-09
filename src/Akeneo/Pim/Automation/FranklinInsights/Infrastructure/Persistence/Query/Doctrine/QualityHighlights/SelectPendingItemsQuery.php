<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Doctrine\DBAL\Connection;

class SelectPendingItemsQuery implements SelectPendingItemIdentifiersQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getUpdatedAttributeCodes(Lock $lock, int $batchSize): array
    {
        return $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE, PendingItemsRepository::ACTION_ENTITY_UPDATED);
    }

    public function getDeletedAttributeCodes(Lock $lock, int $batchSize): array
    {
        return $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE, PendingItemsRepository::ACTION_ENTITY_DELETED);
    }

    public function getUpdatedFamilyCodes(Lock $lock, int $batchSize): array
    {
        return $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_FAMILY, PendingItemsRepository::ACTION_ENTITY_UPDATED);
    }

    public function getDeletedFamilyCodes(Lock $lock, int $batchSize): array
    {
        return $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_FAMILY, PendingItemsRepository::ACTION_ENTITY_DELETED);
    }

    public function getUpdatedProductIds(Lock $lock, int $batchSize): array
    {
        $updatedProductIds = $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_PRODUCT, PendingItemsRepository::ACTION_ENTITY_UPDATED);

        return array_map('intval', $updatedProductIds);
    }

    public function getDeletedProductIds(Lock $lock, int $batchSize): array
    {
        $deletedProductIds = $this->executeQuery($lock, $batchSize, PendingItemsRepository::ENTITY_TYPE_PRODUCT, PendingItemsRepository::ACTION_ENTITY_DELETED);

        return array_map('intval', $deletedProductIds);
    }

    private function executeQuery(Lock $lock, int $limit, string $entityType, string $action): array
    {
        $query = <<<'SQL'
            SELECT entity_id
            FROM pimee_franklin_insights_quality_highlights_pending_items AS pending_items
            WHERE `action` = :action
            AND entity_type = :entity_type
            AND lock_id = :lock
            LIMIT :limit
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'action' => $action,
                'entity_type' => $entityType,
                'lock' => $lock->__toString(),
                'limit' => $limit,
            ],
            [
                'action' => \PDO::PARAM_STR,
                'entity_type' => \PDO::PARAM_STR,
                'lock' => \PDO::PARAM_STR,
                'limit' => \PDO::PARAM_INT,
            ]
        );

        $attributeCodes = [];
        foreach ($statement->fetchAll() as $result) {
            $attributeCodes[] = $result['entity_id'];
        }

        return $attributeCodes;
    }
}

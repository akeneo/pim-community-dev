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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingAttributesIdQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Doctrine\DBAL\Connection;

class SelectPendingItemsQuery implements SelectPendingAttributesIdQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getUpdatedAttributeCodes(int $offsetId, int $batchSize): array
    {
        return $this->executeQuery($offsetId, $batchSize, PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE, PendingItemsRepository::ACTION_ATTRIBUTE_UPDATED);
    }

    public function getDeletedAttributeCodes(int $offsetId, int $batchSize): array
    {
        return $this->executeQuery($offsetId, $batchSize, PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE, PendingItemsRepository::ACTION_ATTRIBUTE_DELETED);
    }

    private function executeQuery(int $offsetId, int $limit, string $entityType, string $action)
    {
        $query = <<<'SQL'
            SELECT id, entity_id
            FROM pimee_franklin_insights_quality_highlights_pending_items AS pending_items
            WHERE `action` = :action
            AND entity_type = :entity_type
            AND locked = :locked
            AND id > :offsetId
            ORDER BY id ASC
            LIMIT :limit
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'action' => $action,
                'entity_type' => $entityType,
                'locked' => PendingItemsRepository::STATUS_UNLOCKED,
                'offsetId' => $offsetId,
                'limit' => $limit,
            ],
            [
                'action' => \PDO::PARAM_STR,
                'entity_type' => \PDO::PARAM_STR,
                'locked' => \PDO::PARAM_INT,
                'offsetId' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]
        );

        $attributeCodes = [];
        foreach ($statement->fetchAll() as $result) {
            $attributeCodes[$result['id']] = $result['entity_id'];
        }

        return $attributeCodes;
    }
}

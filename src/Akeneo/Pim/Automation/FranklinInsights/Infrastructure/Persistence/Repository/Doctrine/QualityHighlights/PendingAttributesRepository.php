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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingAttributesRepositoryInterface;
use Doctrine\DBAL\Connection;

class PendingAttributesRepository implements PendingAttributesRepositoryInterface
{
    public const ACTION_ATTRIBUTE_UPDATED = 1;
    public const ACTION_ATTRIBUTE_DELETED = 2;
    public const ENTITY_TYPE_ATTRIBUTE = 1;
    public const STATUS_LOCKED = 1;
    public const STATUS_UNLOCKED = 0;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function addUpdatedAttributeId(int $id): void
    {
        $sql = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items
(entity_type, entity_id, action, status)
VALUES (:entity_type, :entity_id, :action, :status)
ON DUPLICATE KEY UPDATE action = :action, `date` = CURRENT_TIMESTAMP;
SQL;

        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $id,
            'action' => self::ACTION_ATTRIBUTE_UPDATED,
            'status' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($sql, $bindParams);
    }

    public function addDeletedAttributeId(int $id): void
    {
        $sql = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items
(entity_type, entity_id, action, status)
VALUES (:entity_type, :entity_id, :action, :status)
ON DUPLICATE KEY UPDATE action = :action, `date` = CURRENT_TIMESTAMP;
SQL;

        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $id,
            'action' => self::ACTION_ATTRIBUTE_DELETED,
            'status' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($sql, $bindParams);
    }
}

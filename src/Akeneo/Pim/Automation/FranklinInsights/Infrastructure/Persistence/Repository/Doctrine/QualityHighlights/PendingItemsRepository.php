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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Doctrine\DBAL\Connection;

class PendingItemsRepository implements PendingItemsRepositoryInterface
{
    public const ACTION_ATTRIBUTE_UPDATED = 'update';
    public const ACTION_ATTRIBUTE_DELETED = 'delete';
    public const ENTITY_TYPE_ATTRIBUTE = 'attribute';
    public const STATUS_LOCKED = 1;
    public const STATUS_UNLOCKED = 0;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function addUpdatedAttributeCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $code,
            'action' => self::ACTION_ATTRIBUTE_UPDATED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedAttributeCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $code,
            'action' => self::ACTION_ATTRIBUTE_DELETED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    private function getInsertQuery(): string
    {
        return <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items
(entity_type, entity_id, action, locked)
VALUES (:entity_type, :entity_id, :action, :locked)
ON DUPLICATE KEY UPDATE action = :action;
SQL;
    }
}

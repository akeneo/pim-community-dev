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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Doctrine\DBAL\Connection;

class PendingItemsRepository implements PendingItemsRepositoryInterface
{
    public const ACTION_ENTITY_UPDATED = 'update';
    public const ACTION_ENTITY_DELETED = 'delete';

    public const ENTITY_TYPE_ATTRIBUTE = 'attribute';
    public const ENTITY_TYPE_FAMILY = 'family';
    public const ENTITY_TYPE_PRODUCT = 'product';

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
            'action' => self::ACTION_ENTITY_UPDATED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedAttributeCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_DELETED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addUpdatedFamilyCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_UPDATED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedFamilyCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_DELETED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addUpdatedProductId(int $identifier): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_id' => (string) $identifier,
            'action' => self::ACTION_ENTITY_UPDATED,
            'locked' => self::STATUS_UNLOCKED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedProductId(int $identifier): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_id' => (string) $identifier,
            'action' => self::ACTION_ENTITY_DELETED,
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

    public function fillWithAllAttributes(): void
    {
        $query = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, action)
SELECT :entity_type, code, :action FROM akeneo_pim.pim_catalog_attribute WHERE attribute_type IN (:authorized_attribute_types)
ON DUPLICATE KEY UPDATE action=action;
SQL;

        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'action' => self::ACTION_ENTITY_UPDATED,
            'authorized_attribute_types' => array_keys(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'action' => \PDO::PARAM_STR,
            'authorized_attribute_types' => Connection::PARAM_STR_ARRAY,
        ];

        $this->connection->executeQuery($query, $bindParams, $bindTypes);
    }

    public function fillWithAllFamilies(): void
    {
        $query = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, action)
SELECT :entity_type, code, :action FROM akeneo_pim.pim_catalog_family
ON DUPLICATE KEY UPDATE action=action;

SQL;

        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'action' => self::ACTION_ENTITY_UPDATED,
        ];

        $this->connection->executeQuery($query, $bindParams);
    }

    public function fillWithAllProducts(): void
    {
        // TODO: Implement fillWithAllProducts() method.
    }
}

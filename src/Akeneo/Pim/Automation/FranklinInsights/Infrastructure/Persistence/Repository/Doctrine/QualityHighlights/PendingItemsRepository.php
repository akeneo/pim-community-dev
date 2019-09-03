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
use Ramsey\Uuid\Uuid;

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
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedAttributeCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_DELETED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addUpdatedFamilyCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_UPDATED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedFamilyCode(string $code): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_id' => $code,
            'action' => self::ACTION_ENTITY_DELETED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addUpdatedProductId(int $identifier): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_id' => (string) $identifier,
            'action' => self::ACTION_ENTITY_UPDATED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function addDeletedProductId(int $identifier): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_id' => (string) $identifier,
            'action' => self::ACTION_ENTITY_DELETED,
        ];

        $this->connection->executeQuery($this->getInsertQuery(), $bindParams);
    }

    public function acquireLock(Uuid $lockUUID): void
    {
        $lockQuery = <<<'SQL'
UPDATE pimee_franklin_insights_quality_highlights_pending_items
SET lock_uuid=:lock_uuid
WHERE lock_uuid = ''
SQL;

        $bindParams = [
            'lock_uuid' => $lockUUID->toString(),
        ];

        $this->connection->executeQuery($lockQuery, $bindParams);
    }

    public function removeUpdatedAttributes(array $attributeCodes, Uuid $lockUUID): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock_uuid' => $lockUUID->toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock_uuid' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeDeletedAttributes(array $attributeCodes, Uuid $lockUUID): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock_uuid' => $lockUUID->toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock_uuid' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeUpdatedFamilies(array $familyCodes, Uuid $lockUUID): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock_uuid' => $lockUUID->toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock_uuid' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeDeletedFamilies(array $familyCodes, Uuid $lockUUID): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock_uuid' => $lockUUID->toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock_uuid' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    private function getInsertQuery(): string
    {
        return <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items
(entity_type, entity_id, action)
VALUES (:entity_type, :entity_id, :action)
ON DUPLICATE KEY UPDATE action = :action;
SQL;
    }

    private function getDeleteQuery(): string
    {
        return <<<'SQL'
DELETE FROM pimee_franklin_insights_quality_highlights_pending_items
WHERE lock_uuid=:lock_uuid AND entity_id IN (:entity_ids) AND entity_type=:entity_type AND action=:action
SQL;
    }

    public function fillWithAllAttributes(): void
    {
        $query = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_id, entity_type, action)
SELECT code, :entity_type, :action FROM akeneo_pim.pim_catalog_attribute WHERE attribute_type IN (:authorized_attribute_types)
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
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_id, entity_type, action)
SELECT code, :entity_type, :action FROM akeneo_pim.pim_catalog_family
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
        $query = <<<'SQL'
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_id, entity_type, action)
SELECT id, :entity_type, :action FROM akeneo_pim.pim_catalog_product WHERE is_enabled=1 AND product_model_id IS NULL
ON DUPLICATE KEY UPDATE action=action;
SQL;

        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'action' => self::ACTION_ENTITY_UPDATED,
        ];

        $this->connection->executeQuery($query, $bindParams);
    }
}

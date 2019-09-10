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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Doctrine\DBAL\Connection;

class PendingItemsRepository implements PendingItemsRepositoryInterface
{
    public const ACTION_ENTITY_UPDATED = 'update';
    public const ACTION_ENTITY_DELETED = 'delete';

    public const ENTITY_TYPE_ATTRIBUTE = 'attribute';
    public const ENTITY_TYPE_FAMILY = 'family';
    public const ENTITY_TYPE_PRODUCT = 'product';

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

    public function acquireLock(Lock $lock): void
    {
        $lockQuery = <<<'SQL'
UPDATE pimee_franklin_insights_quality_highlights_pending_items
SET lock_id=:lock
WHERE lock_id = ''
SQL;

        $bindParams = [
            'lock' => $lock->__toString(),
        ];

        $this->connection->executeQuery($lockQuery, $bindParams);
    }

    public function removeUpdatedAttributes(array $attributeCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeDeletedAttributes(array $attributeCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeUpdatedFamilies(array $familyCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeDeletedFamilies(array $familyCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeUpdatedProducts(array $productIds, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_ids' => $productIds,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function removeDeletedProducts(array $productIds, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_ids' => $productIds,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
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
WHERE lock_id=:lock AND entity_id IN (:entity_ids) AND entity_type=:entity_type AND action=:action
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

    public function releaseUpdatedAttributesLock(array $attributeCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function releaseDeletedAttributesLock(array $attributeCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
            'entity_ids' => $attributeCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function releaseUpdatedFamiliesLock(array $familyCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function releaseDeletedFamiliesLock(array $familyCodes, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_FAMILY,
            'entity_ids' => $familyCodes,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function releaseUpdatedProductsLock(array $productIds, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_ids' => $productIds,
            'action' => self::ACTION_ENTITY_UPDATED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    public function releaseDeletedProductsLock(array $productIds, Lock $lock): void
    {
        $bindParams = [
            'entity_type' => self::ENTITY_TYPE_PRODUCT,
            'entity_ids' => $productIds,
            'action' => self::ACTION_ENTITY_DELETED,
            'lock' => $lock->__toString(),
        ];

        $bindTypes = [
            'entity_type' => \PDO::PARAM_STR,
            'entity_ids' => Connection::PARAM_STR_ARRAY,
            'action' => \PDO::PARAM_STR,
            'lock' => \PDO::PARAM_STR,
        ];

        $this->connection->executeQuery($this->getReleaseLockQuery(), $bindParams, $bindTypes);
        $this->connection->executeQuery($this->getDeleteQuery(), $bindParams, $bindTypes);
    }

    private function getReleaseLockQuery(): string
    {
        return <<<'SQL'
UPDATE IGNORE pimee_franklin_insights_quality_highlights_pending_items
SET lock_id = ''
WHERE entity_id IN (:entity_ids) AND entity_type=:entity_type AND action=:action
SQL;
    }
}

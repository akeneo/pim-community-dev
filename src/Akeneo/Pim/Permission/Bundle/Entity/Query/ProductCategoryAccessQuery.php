<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Returns the granted product depending on category access
 */
class ProductCategoryAccessQuery implements ProductCategoryAccessQueryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
WITH
existing_product AS (
    SELECT uuid, product_model_id FROM pim_catalog_product WHERE pim_catalog_product.identifier IN (:product_identifiers)
),
product_category AS (
    SELECT p.uuid AS product_uuid, mc.category_id
    FROM
        existing_product p
        INNER JOIN (
            SELECT
                p.uuid, cp.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
            UNION ALL
            SELECT
                p.uuid, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = sub.id
            UNION ALL
            SELECT
                p.uuid, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_product_model root ON root.id = sub.parent_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = root.id
        ) AS mc ON mc.uuid = p.uuid
)
SELECT DISTINCT pim_catalog_product.identifier
FROM product_category
    INNER JOIN pimee_security_product_category_access category_access ON category_access.category_id = product_category.category_id
    INNER join pim_catalog_product on product_category.product_uuid = pim_catalog_product.uuid
        AND category_access.user_group_id IN (:user_groups_ids)
WHERE category_access.view_items = 1
UNION DISTINCT
SELECT p.identifier
FROM pim_catalog_product p
WHERE p.identifier IN (:product_identifiers) AND NOT EXISTS (SELECT 1 FROM product_category cp WHERE cp.product_uuid = p.uuid)
SQL;

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $query,
            [
                'user_groups_ids' => $user->getGroupsIds(),
                'product_identifiers' => $productIdentifiers
            ],
            [
                'user_groups_ids' => Connection::PARAM_INT_ARRAY,
                'product_identifiers' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_column($stmt->fetchAllAssociative(), 'identifier');
    }

    public function getGrantedProductUuids(array $productUuids, UserInterface $user): array
    {
        if (empty($productUuids)) {
            return [];
        }
        Assert::allIsInstanceOf($productUuids, UuidInterface::class);

        $query = <<<SQL
WITH
existing_product AS (
    SELECT uuid, product_model_id FROM pim_catalog_product WHERE pim_catalog_product.uuid IN (:product_uuids)
),
product_category AS (
    SELECT p.uuid AS product_uuid, mc.category_id
    FROM
        existing_product p
        INNER JOIN (
            SELECT
                p.uuid, cp.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
            UNION ALL
            SELECT
                p.uuid, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = sub.id
            UNION ALL
            SELECT
                p.uuid, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_product_model root ON root.id = sub.parent_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = root.id
        ) AS mc ON mc.uuid = p.uuid
)
SELECT DISTINCT BIN_TO_UUID(pim_catalog_product.uuid) AS uuid
FROM product_category
    INNER JOIN pimee_security_product_category_access category_access ON category_access.category_id = product_category.category_id
    INNER join pim_catalog_product on product_category.product_uuid = pim_catalog_product.uuid
        AND category_access.user_group_id IN (:user_groups_ids)
WHERE category_access.view_items = 1
UNION DISTINCT
SELECT BIN_TO_UUID(p.uuid) AS uuid
FROM pim_catalog_product p
WHERE p.uuid IN (:product_uuids) AND NOT EXISTS (SELECT 1 FROM product_category cp WHERE cp.product_uuid = p.uuid)
SQL;

        $productUuidsAsBytes = \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $query,
            [
                'user_groups_ids' => $user->getGroupsIds(),
                'product_uuids' => $productUuidsAsBytes
            ],
            [
                'user_groups_ids' => Connection::PARAM_INT_ARRAY,
                'product_uuids' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_map(
            fn (string $uuid): UuidInterface => Uuid::fromString($uuid),
            array_column($stmt->fetchAllAssociative(), 'uuid')
        );
    }
}

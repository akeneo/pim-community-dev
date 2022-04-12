<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Returns the granted product depending on category access
 */
class ProductCategoryAccessQuery implements ProductCategoryAccessQueryInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * The query is an union of products belonging to categories where the given user has access
     * and products without category.
     *
     * @param array $productIdentifiers
     * @param UserInterface $user
     *
     * @return int[]
     */
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
WITH
existing_product AS (
    SELECT id, product_model_id FROM pim_catalog_product WHERE pim_catalog_product.identifier IN (:product_ids)
),
product_category AS (
    SELECT p.id AS product_id, mc.category_id
    FROM
        existing_product p
        INNER JOIN (
            SELECT
                p.id, cp.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
            UNION ALL
            SELECT
                p.id, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = sub.id
            UNION ALL
            SELECT
                p.id, cpm.category_id AS category_id
            FROM existing_product p
                INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                INNER JOIN pim_catalog_product_model root ON root.id = sub.parent_id
                INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = root.id
        ) AS mc ON mc.id = p.id
)
SELECT DISTINCT pim_catalog_product.identifier
FROM product_category
    INNER JOIN pimee_security_product_category_access category_access ON category_access.category_id = product_category.category_id
    INNER join pim_catalog_product on product_category.product_id = pim_catalog_product.id
        AND category_access.user_group_id IN (:user_groups_ids)
WHERE category_access.view_items = 1
UNION DISTINCT
SELECT p.identifier
FROM pim_catalog_product p
WHERE p.identifier IN (:product_ids) AND NOT EXISTS (SELECT 1 FROM product_category cp WHERE cp.product_id = p.id)
SQL;

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $query,
            [
                'user_groups_ids' => $user->getGroupsIds(),
                'product_ids' => $productIdentifiers
            ],
            [
                'user_groups_ids' => Connection::PARAM_INT_ARRAY,
                'product_ids' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_column($stmt->fetchAllAssociative(), 'identifier');
    }
}

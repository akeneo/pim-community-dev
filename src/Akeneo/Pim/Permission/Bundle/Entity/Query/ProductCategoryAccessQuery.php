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
        SELECT p.identifier
        FROM pim_catalog_product p
        INNER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
        INNER JOIN pimee_security_product_category_access pca
            ON pca.category_id = cp.category_id
            AND pca.user_group_id IN (:user_groups_ids)
        WHERE p.identifier IN (:product_ids)
        AND p.identifier IS NOT NULL
        AND pca.view_items = 1
        UNION
        SELECT p.identifier
        FROM pim_catalog_product p
        WHERE p.identifier IN (:product_ids) AND NOT EXISTS (
            SELECT 1
            FROM pim_catalog_category_product cp
            WHERE cp.product_id = p.id
        )
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

        return array_column($stmt->fetchAll(), 'identifier');
    }
}

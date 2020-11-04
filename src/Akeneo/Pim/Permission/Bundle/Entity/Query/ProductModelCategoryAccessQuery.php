<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Returns the granted product models depending on category access
 */
class ProductModelCategoryAccessQuery implements ProductModelCategoryAccessQueryInterface
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
     * The query is an union of product models belonging to categories where the given user has access
     * and product models without category.
     *
     * @param array $productModelCodes
     * @param UserInterface $user
     *
     * @return int[]
     */
    public function getGrantedProductModelCodes(array $productModelCodes, UserInterface $user): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $query = <<<SQL
            SELECT pm.code
            FROM pim_catalog_product_model pm
            INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = pm.id
            INNER JOIN pimee_security_product_category_access pca
                ON pca.category_id = cpm.category_id
                AND pca.user_group_id IN (:user_groups_ids)
            WHERE pm.code IN (:product_model_codes)
            AND pm.code IS NOT NULL
            AND pca.view_items = 1
            UNION
            SELECT pm.code
            FROM pim_catalog_product_model pm
            WHERE pm.code IN (:product_model_codes) AND NOT EXISTS (
                SELECT 1
                FROM pim_catalog_category_product_model cpm
                WHERE cpm.product_model_id = pm.id
            )
SQL;

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $query,
            [
                'user_groups_ids' => $user->getGroupsIds(),
                'product_model_codes' => $productModelCodes
            ],
            [
                'user_groups_ids' => Connection::PARAM_INT_ARRAY,
                'product_model_codes' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_column($stmt->fetchAll(), 'code');
    }
}

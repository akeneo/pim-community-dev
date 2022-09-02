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

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * Returns the granted item depending on category access
 */
class ItemCategoryAccessQuery
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $itemTableName;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, string $itemTableName)
    {
        $this->entityManager = $entityManager;
        $this->itemTableName = $itemTableName;
    }

    /**
     * @return array<int, int>
     */
    public function getGrantedItemIds(array $items, UserInterface $user): array
    {
        if (empty($items)) {
            return [];
        }

        Assert::false(
            current($items) instanceof ProductInterface && !current($items) instanceof PublishedProductInterface,
            'This method does not work for products. Please use getGrantedProductUuids instead.'
        );

        $itemMetadata = $this->entityManager->getClassMetadata($this->itemTableName);

        $categoryAssoc = $itemMetadata->getAssociationMapping('categories');

        $categoryTableName = $categoryAssoc['joinTable']['name'];
        $itemTableName = $itemMetadata->getTableName();
        $relation = key($categoryAssoc['relationToSourceKeyColumns']);

        $sql = sprintf(
            '
            SELECT category_item.%s AS id
            FROM %s category_item
            INNER JOIN pimee_security_product_category_access category_access ON category_access.category_id = category_item.category_id 
            AND category_access.user_group_id IN (?)
            WHERE category_item.%s IN (?) AND category_item.%s IS NOT NULL AND category_access.view_items = 1
            UNION
            SELECT item.id
            FROM %s item
            WHERE item.id IN (?) AND NOT EXISTS (SELECT 1 FROM %s cp WHERE cp.%s = item.id)',
            $relation,
            $categoryTableName,
            $relation,
            $relation,
            $itemTableName,
            $categoryTableName,
            $relation
        );

        $itemIds = array_map(function ($item) {
            return $item->getId();
        }, $items);

        $groupIds = array_map(function ($group) {
            return $group->getId();
        }, $user->getGroups()->toArray());

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [$groupIds, $itemIds, $itemIds],
            [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]
        );

        $grantedItemIds = [];
        foreach ($stmt->fetchAllAssociative() as $id) {
            $grantedItemIds[$id['id']] = $id['id'];
        }

        return $grantedItemIds;
    }

    /**
     * @return string[]
     */
    public function getGrantedProductUuids(array $products, UserInterface $user): array
    {
        if (empty($products)) {
            return [];
        }

        $sql = <<<SQL
        WITH
        existing_product AS (
            SELECT uuid, product_model_id FROM pim_catalog_product WHERE uuid IN (:product_uuids)
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
        SELECT BIN_TO_UUID(product_category.product_uuid) AS uuid
        FROM product_category
            INNER JOIN pimee_security_product_category_access category_access ON category_access.category_id = product_category.category_id 
                                                                             AND category_access.user_group_id IN (:group_ids)
        WHERE product_category.product_uuid IN (:product_uuids) AND product_category.product_uuid IS NOT NULL AND category_access.view_items = 1
        UNION DISTINCT
        SELECT BIN_TO_UUID(p.uuid) AS uuid
        FROM pim_catalog_product p
        WHERE p.uuid IN (:product_uuids) AND NOT EXISTS (SELECT 1 FROM product_category cp WHERE cp.product_uuid = p.uuid)
        SQL;

        $productUuidsAsBytes = array_map(
            fn (ProductInterface $product): string => $product->getUuid()->getBytes(),
            $products
        );

        $groupIds = array_map(function ($group) {
            return $group->getId();
        }, $user->getGroups()->toArray());

        $stmt = $this->entityManager->getConnection()->executeQuery(
            $sql,
            ['group_ids' => $groupIds, 'product_uuids' => $productUuidsAsBytes],
            ['group_ids' => Connection::PARAM_INT_ARRAY, 'product_uuids' => Connection::PARAM_STR_ARRAY]
        );

        return $stmt->fetchFirstColumn();
    }
}

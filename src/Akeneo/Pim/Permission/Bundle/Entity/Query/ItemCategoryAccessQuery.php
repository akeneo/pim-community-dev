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

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

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
     * @return int[]
     */
    public function getGrantedItemIds(array $items, UserInterface $user): array
    {
        if (empty($items)) {
            return [];
        }

        $itemMetadata = $this->entityManager->getClassMetadata($this->itemTableName);

        $categoryAssoc = $itemMetadata->getAssociationMapping('categories');

        $categoryTableName = $categoryAssoc['joinTable']['name'];
        $itemTableName = $itemMetadata->getTableName();
        $relation = key($categoryAssoc['relationToSourceKeyColumns']);

        $sql = sprintf('
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
        foreach ($stmt->fetchAll() as $id) {
            $grantedItemIds[$id['id']] = $id['id'];
        }

        return $grantedItemIds;
    }
}

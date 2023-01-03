<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Application\Query;

use Doctrine\DBAL\Connection;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class RemoveCategoryProductPermissions implements RemoveCategoryProductPermissionsInterface
{
    private const VIEW_ITEMS = 'view_items';
    private const EDIT_ITEMS = 'edit_items';
    private const OWN_ITEMS = 'own_items';

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param array<string, array<int>> $permissions
     */
    public function __invoke(int $categoryId, array $permissions): void
    {
        $this->updatePermission(self::VIEW_ITEMS, $categoryId, $permissions['view'] ?? []);
        $this->updatePermission(self::EDIT_ITEMS, $categoryId, $permissions['edit'] ?? []);
        $this->updatePermission(self::OWN_ITEMS, $categoryId, $permissions['own'] ?? []);

        $this->cleanRecords($categoryId);
    }

    private function updatePermission(string $itemsType, int $categoryId, array $userGroupIds): void
    {
        if (empty($userGroupIds)) {
            return;
        }

        $query = <<<SQL
            UPDATE pimee_security_product_category_access
            SET $itemsType = 0
            WHERE category_id = :category_id
            AND user_group_id IN (:user_group_ids);
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'category_id' => $categoryId,
                'user_group_ids' => $userGroupIds
            ],
            [
                'category_id' => \PDO::PARAM_INT,
                'user_group_ids' => Connection::PARAM_INT_ARRAY,
            ],
        );
    }

    private function cleanRecords(int $categoryId): void
    {
        $query = <<<SQL
            DELETE FROM pimee_security_product_category_access
            WHERE category_id = :category_id
            AND view_items = 0
            AND edit_items = 0
            AND own_items = 0;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'category_id' => $categoryId,
            ],
            [
                'category_id' => \PDO::PARAM_INT,
            ],
        );
    }
}

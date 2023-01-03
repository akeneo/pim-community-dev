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
class SaveCategoryProductPermissions implements SaveCategoryProductPermissionsInterface
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
        $this->updatePermission(self::VIEW_ITEMS, $categoryId, $permissions['view']);
        $this->updatePermission(self::EDIT_ITEMS, $categoryId, $permissions['edit']);
        $this->updatePermission(self::OWN_ITEMS, $categoryId, $permissions['own']);
    }

    private function updatePermission(string $itemsType, int $categoryId, array $userGroupIds): void
    {
        if (empty($userGroupIds)) {
            return;
        }

        $placeholders = \implode(',', \array_fill(0, \count($userGroupIds), '(?, ?, ?)'));
        $statement = $this->connection->prepare(
            <<<SQL
            INSERT INTO pimee_security_product_category_access (user_group_id, category_id, $itemsType)
            VALUES {$placeholders}
            ON DUPLICATE KEY UPDATE $itemsType = VALUES($itemsType);
            SQL
        );

        $placeholderIndex = 0;
        foreach ($userGroupIds as $userGroupId) {
            $statement->bindValue(++$placeholderIndex, $userGroupId);
            $statement->bindValue(++$placeholderIndex, $categoryId);
            $statement->bindValue(++$placeholderIndex, 1);
        }

        $statement->executeQuery();
    }
}

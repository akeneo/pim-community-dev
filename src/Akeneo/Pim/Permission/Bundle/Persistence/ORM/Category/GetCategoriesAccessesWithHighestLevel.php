<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Akeneo\Pim\Permission\Component\Attributes;
use Doctrine\DBAL\Connection;

class GetCategoriesAccessesWithHighestLevel
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @return array<string, string>
     *
     * @throws \LogicException
     */
    public function execute(int $groupId): array
    {
        $query = <<<SQL
SELECT pim_catalog_category.code, view_items AS view, edit_items AS edit, own_items AS own
FROM pimee_security_product_category_access
JOIN pim_catalog_category ON pim_catalog_category.id = pimee_security_product_category_access.category_id
WHERE user_group_id = :user_group_id
SQL;

        $rows = $this->connection->fetchAllAssociative($query, [
            'user_group_id' => $groupId,
        ]) ?: [];

        $results = [];
        foreach ($rows as $row) {
            $results[$row['code']] = $this->getHighestAccessLevel($row);
        }

        return $results;
    }

    /**
     * @param array{
     *     own: bool,
     *     edit: bool,
     *     view: bool
     * } $row
     *
     * @throws \LogicException
     */
    private function getHighestAccessLevel(array $row): ?string
    {
        if ($row['own']) {
            return Attributes::OWN_PRODUCTS;
        } elseif ($row['edit']) {
            return Attributes::EDIT_ITEMS;
        } elseif ($row['view']) {
            return Attributes::VIEW_ITEMS;
        }

        throw new \LogicException('Category access level is unknown');
    }
}

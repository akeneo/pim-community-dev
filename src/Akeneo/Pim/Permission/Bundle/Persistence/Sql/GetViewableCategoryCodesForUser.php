<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql;

use Akeneo\Pim\Permission\Component\Query\GetViewableCategoryCodesForUserInterface;
use Doctrine\DBAL\Connection;

class GetViewableCategoryCodesForUser implements GetViewableCategoryCodesForUserInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function forCategoryCodes(array $categoryCodes, int $userId): array
    {
        if (empty($categoryCodes)) {
            return [];
        }

        $query = <<<SQL
            SELECT category.code
            FROM pim_catalog_category category
            WHERE EXISTS (
                SELECT *
                FROM pimee_security_product_category_access category_access
                INNER JOIN oro_user_access_group user_access_group on category_access.user_group_id = user_access_group.group_id AND user_access_group.user_id = :userId
                WHERE category_access.category_id = category.id
            )
            AND category.code IN (:categoryCodes);
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'userId' => $userId,
            'categoryCodes' => $categoryCodes
        ], ['categoryCodes' => Connection::PARAM_STR_ARRAY]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN, 'code');
    }
}

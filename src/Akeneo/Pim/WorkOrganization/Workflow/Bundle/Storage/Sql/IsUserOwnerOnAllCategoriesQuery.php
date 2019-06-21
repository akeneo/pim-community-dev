<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\IsUserOwnerOnAllCategoriesQueryInterface;
use Doctrine\DBAL\Connection;

class IsUserOwnerOnAllCategoriesQuery implements IsUserOwnerOnAllCategoriesQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $userName, array $categories): bool
    {
        if (empty($categories)) {
            return true;
        }

        $sql = <<<SQL
SELECT EXISTS(
    SELECT 1 
    FROM oro_user u
        CROSS JOIN  pim_catalog_category c
        INNER JOIN oro_user_access_group uag ON uag.user_id = u.id
        INNER JOIN pimee_security_product_category_access pca ON pca.category_id = c.id AND pca.user_group_id = uag.group_id
    WHERE u.username = :userName
      AND c.code IN (:categories)
      AND pca.own_items = 0
) AS own_category; 
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'userName' => $userName,
                'categories' => $categories
            ],
            [
                'userName' => \PDO::PARAM_STR,
                'categories' => Connection::PARAM_STR_ARRAY
            ]
        );

        return !(bool) $statement->fetchColumn(0);
    }
}

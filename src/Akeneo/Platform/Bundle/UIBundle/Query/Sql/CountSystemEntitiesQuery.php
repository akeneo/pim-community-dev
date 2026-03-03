<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Query\Sql;

use Akeneo\Platform\Bundle\UIBundle\Query\CountSystemEntitiesQueryInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountSystemEntitiesQuery implements CountSystemEntitiesQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): array
    {
        $anonymousRole = User::ROLE_ANONYMOUS;
        $query = <<<SQL
SELECT
    (SELECT COUNT(*) FROM oro_user WHERE user_type = 'user') AS count_users,
    (SELECT COUNT(*) FROM oro_access_group WHERE name != 'All' AND type = 'default') AS count_user_groups,
    (SELECT COUNT(*) FROM oro_access_role WHERE role != "$anonymousRole" AND type = 'default') AS count_roles,
    (
        SELECT SUM(JSON_EXTRACT(volume, '$.value')) AS count_product_values 
        FROM pim_aggregated_volume 
        WHERE volume_name IN ('count_product_values', 'count_product_model_values')
    ) as count_product_values
SQL;

        $result = $this->dbConnection->executeQuery($query)->fetchAssociative();

        return array_map(fn ($rawCount) => intval($rawCount), $result);
    }
}

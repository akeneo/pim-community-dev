<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\UIBundle\Query\Sql;

use Akeneo\Platform\Bundle\UIBundle\Query\CountSettingsEntitiesQueryInterface;
use Doctrine\DBAL\Connection;

final class CountEnterpriseSettingsEntitiesQuery implements CountSettingsEntitiesQueryInterface
{
    private Connection $dbConnection;

    private CountSettingsEntitiesQueryInterface $countSettingsEntitiesQuery;

    public function __construct(Connection $dbConnection, CountSettingsEntitiesQueryInterface $countSettingsEntitiesQuery)
    {
        $this->dbConnection = $dbConnection;
        $this->countSettingsEntitiesQuery = $countSettingsEntitiesQuery;
    }

    public function execute(): array
    {
        $communitySettingsEntities = $this->countSettingsEntitiesQuery->execute();

        $query = <<<SQL
SELECT
    (SELECT COUNT(*) FROM akeneo_rule_engine_rule_definition WHERE enabled = 1) AS count_rules;

SQL;

        $enterpriseSettingsEntities = $this->dbConnection->executeQuery($query)->fetch(\PDO::FETCH_ASSOC);
        $enterpriseSettingsEntities = array_map(fn ($rawCount) => intval($rawCount), $enterpriseSettingsEntities);

        return array_merge($communitySettingsEntities, $enterpriseSettingsEntities);
    }
}

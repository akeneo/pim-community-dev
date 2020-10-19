<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeGroupsActivationQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllAttributeGroupsActivationQuery implements GetAllAttributeGroupsActivationQueryInterface
{
    /** @var Connection */
    protected $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT attribute_group_code, activated 
FROM pim_catalog_attribute_group
INNER JOIN pim_data_quality_insights_attribute_group_activation ON(pim_catalog_attribute_group.code = pim_data_quality_insights_attribute_group_activation.attribute_group_code);
SQL;

        $result = $this->dbConnection->executeQuery($query);

        $groups = [];
        foreach ($result as $row) {
            $groups[$row['attribute_group_code']] = (bool) $row['activated'];
        }

        return $groups;
    }
}

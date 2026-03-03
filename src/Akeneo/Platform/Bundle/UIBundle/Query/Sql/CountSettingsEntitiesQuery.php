<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Query\Sql;

use Akeneo\Platform\Bundle\UIBundle\Query\CountSettingsEntitiesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountSettingsEntitiesQuery implements CountSettingsEntitiesQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT
    (SELECT COUNT(*) FROM pim_catalog_category) AS count_categories,
    (SELECT COUNT(*) FROM pim_catalog_category WHERE parent_id IS NULL) AS count_category_trees,
    (SELECT COUNT(*) FROM pim_catalog_channel) AS count_channels,
    (SELECT COUNT(*) FROM pim_catalog_locale WHERE is_activated = 1) AS count_locales,
    (SELECT COUNT(*) FROM pim_catalog_currency WHERE is_activated = 1) AS count_currencies,
    (SELECT COUNT(*) FROM pim_catalog_attribute_group) AS count_attribute_groups,
    (SELECT COUNT(*) FROM pim_catalog_attribute) AS count_attributes,
    (SELECT COUNT(*) FROM pim_catalog_family) AS count_families,
    (SELECT COUNT(*) FROM akeneo_measurement) AS count_measurements,
    (SELECT COUNT(*) FROM pim_catalog_association_type) AS count_association_types,
    (SELECT COUNT(*) FROM pim_catalog_group_type) AS count_group_types,
    (SELECT COUNT(*) FROM pim_catalog_group) AS count_groups,
    (SELECT COUNT(*) FROM pim_catalog_identifier_generator) AS count_identifier_generators
SQL;

        $result = $this->dbConnection->executeQuery($query)->fetchAssociative();

        return array_map(fn ($rawCount) => intval($rawCount), $result);
    }
}

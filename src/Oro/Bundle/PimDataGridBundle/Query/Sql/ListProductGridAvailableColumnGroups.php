<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\PimDataGridBundle\Query\ListProductGridAvailableColumnGroups as ListProductGridAvailableColumnGroupsQuery;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListProductGridAvailableColumnGroups implements ListProductGridAvailableColumnGroupsQuery
{
    /** @var Connection */
    private $connection;

    /** @var ConfigurationProviderInterface */
    private $configurationProvider;

    /**
     * @param Connection                     $connection
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function __construct(Connection $connection, ConfigurationProviderInterface $configurationProvider)
    {
        $this->connection = $connection;
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $locale, int $userId): array
    {
        $datagridConfiguration = $this->configurationProvider->getConfiguration('product-grid');

        $systemColumns = $datagridConfiguration->offsetGetByPath(
            sprintf('[%s]', Configuration::COLUMNS_KEY), []
        ) + $datagridConfiguration->offsetGetByPath(
            sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY), []
        );

        $columnGroups = [[
            'code'  => 'system',
            'count' => count($systemColumns),
            'label' => 'System',
        ]];

        /*
         * We need to exclude the attributes that could have the same code as a system column.
         * This should not happen, but some reserved codes have been forgotten in the validation of attribute creation.
         * This will not be needed anymore when the validation will be fixed.
         */
        $attributesToExclude = array_keys($systemColumns);

        $sql = <<<SQL
SELECT DISTINCT g.code, g.sort_order, attributes.attributes_count,
  COALESCE(trans.label, CONCAT('[', g.code, ']')) AS label
FROM pim_catalog_attribute_group AS g
JOIN
(
    SELECT att.group_id, COUNT(att.id) AS attributes_count
    FROM pim_catalog_attribute AS att
    WHERE att.useable_as_grid_filter = 1 AND att.code NOT IN (:attributesToExclude)
    GROUP BY att.group_id
) AS attributes ON g.id = attributes.group_id
LEFT JOIN pim_catalog_attribute_group_translation AS trans ON g.id = trans.foreign_key AND trans.locale = :locale
ORDER BY g.sort_order ASC;
SQL;

        $stmt = $this->connection->executeQuery($sql,
            [
                'locale'              => $locale,
                'attributesToExclude' => $attributesToExclude,
            ],
            [
                'attributesToExclude' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $attributeGroups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($attributeGroups as $attributeGroup) {
            $columnGroups[] = [
                'code'  => $attributeGroup['code'],
                'count' => (int) $attributeGroup['attributes_count'],
                'label' => $attributeGroup['label'],
            ];
        }

        return $columnGroups;
    }
}

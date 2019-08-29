<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\PimDataGridBundle\Query\ListProductGridAvailableColumns as ListProductGridAvailableColumnsQuery;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListProductGridAvailableColumns implements ListProductGridAvailableColumnsQuery
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
    public function fetch(string $locale, int $page, string $groupCode, string $searchOnLabel, int $userId): array
    {
        $page = max($page, 1);
        $offset = ($page - 1) * ListProductGridAvailableColumnsQuery::COLUMNS_PER_PAGE;
        $limit = ListProductGridAvailableColumnsQuery::COLUMNS_PER_PAGE;

        $systemColumns = [];
        if ('' === $groupCode || 'system' === $groupCode) {
            $systemColumns = $this->fetchSystemColumns($searchOnLabel);
            $systemColumnsTotalCount = count($systemColumns);
            $systemColumns = array_slice($systemColumns, $offset, $limit);
            $offset = max(0, $offset - $systemColumnsTotalCount);
            $limit -= count($systemColumns);
        }

        $attributeColumns = [];
        if ($limit > 0) {
            $attributeColumns = $this->fetchAttributesAsColumn($locale, $limit, $offset, $groupCode, $searchOnLabel);
        }

        return array_replace($systemColumns, $attributeColumns);
    }

    /**
     * @return array
     */
    private function getColumnsFromProductGridConfiguration(): array
    {
        $datagridConfiguration = $this->configurationProvider->getConfiguration('product-grid');

        $propertiesColumns = $datagridConfiguration->offsetGetByPath(
            sprintf('[%s]', Configuration::COLUMNS_KEY), []
        );

        $otherColumns = $datagridConfiguration->offsetGetByPath(
            sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY), []
        );

        return $propertiesColumns + $otherColumns;
    }

    /**
     * @param string $searchOnLabel
     *
     * @return array
     */
    private function fetchSystemColumns(string $searchOnLabel): array
    {
        $configurationColumns = $this->getColumnsFromProductGridConfiguration();

        $systemColumns = [];
        foreach ($configurationColumns as $code => $column) {
            $systemColumns[$code] = [
                'code'  => $code,
                'label' => $column['label']
            ];
        }

        if ('' !== $searchOnLabel) {
            $systemColumns = array_filter($systemColumns, function ($property) use ($searchOnLabel) {
                return false !== stripos($property['label'], $searchOnLabel);
            });
        }

        return $systemColumns;
    }

    /**
     * @param string $locale
     * @param int    $limit
     * @param int    $offset
     * @param string $groupCode
     * @param string $searchOnLabel
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchAttributesAsColumn(string $locale, int $limit, int $offset, string $groupCode, string $searchOnLabel): array
    {
        $sqlSearch = '';
        if ('' !== $searchOnLabel) {
            $sqlSearch = 'AND COALESCE(att_trans.label, att.code) LIKE :search';
        }
        if ('' !== $groupCode) {
            $sqlSearch .= ' AND g.code = :groupCode';
        }
        $sql = <<<SQL
SELECT DISTINCT att.code, att.sort_order AS attribute_order, g.sort_order, g.sort_order AS group_order,
  COALESCE(att_trans.label, CONCAT('[', att.code, ']')) AS label
FROM pim_catalog_attribute AS att
INNER JOIN pim_catalog_attribute_group AS g ON att.group_id = g.id
LEFT JOIN pim_catalog_attribute_translation AS att_trans ON att.id = att_trans.foreign_key AND att_trans.locale = :locale
WHERE att.useable_as_grid_filter = 1 AND att.code NOT IN (:attributesToExclude) $sqlSearch
ORDER BY g.sort_order ASC, att.sort_order ASC, label ASC
LIMIT $limit OFFSET $offset
SQL;

        /*
         * We need to exclude the attributes that could have the same code as a system column.
         * This should not happen, but some reserved codes have been forgotten in the validation of attribute creation.
         * This will not be needed anymore when the validation will be fixed.
         */
        $attributesToExclude = array_keys($this->getColumnsFromProductGridConfiguration());

        $queryParameters = [
            'locale' => $locale,
            'attributesToExclude' => $attributesToExclude,
        ];
        $queryParametersTypes = [
            'attributesToExclude' => Connection::PARAM_STR_ARRAY,
        ];

        if ('' !== $searchOnLabel) {
            $queryParameters['search'] = "%$searchOnLabel%";
        }
        if ('' !== $groupCode) {
            $queryParameters['groupCode'] = $groupCode;
        }

        $stmt = $this->connection->executeQuery($sql, $queryParameters, $queryParametersTypes);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $attributes = [];
        foreach ($results as $resultRow) {
            $attributes[$resultRow['code']] = [
                'code'  => $resultRow['code'],
                'label' => $resultRow['label'],
            ];
        }

        return $attributes;
    }
}

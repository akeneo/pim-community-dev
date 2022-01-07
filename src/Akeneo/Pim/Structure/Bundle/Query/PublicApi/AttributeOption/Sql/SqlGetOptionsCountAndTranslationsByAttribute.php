<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationsByAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetOptionsCountAndTranslationsByAttribute implements GetOptionsCountAndTranslationsByAttribute
{
    private const MAX_PAGE_SIZE = 10;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    //todo create really precise PHPDoc for return type

    /**
     * @param SearchAttributeOptionsParameters $searchParameters
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(SearchAttributeOptionsParameters $searchParameters): array
    {
        $search = $searchParameters->getSearch();
        $size = $searchParameters->getLimit();
        $offset = $searchParameters->getOffset();
        $locale = $searchParameters->getLocale();

        $queryLimit = '';
        $queryOffset = '';
        $querySearch = '';

        if (!is_null($size)) {
            $queryLimit = 'LIMIT :limit';
            if (!is_null($offset) && $offset > 0) {
                $queryOffset = 'OFFSET :offset';
            }
        }

        if (!empty($search)) {
            $search = strtolower($search);
            $querySearch = 'AND LOWER(labels->:locale) LIKE :search';
        }

        $query = <<<SQL
WITH attribute_labels AS (
    SELECT DISTINCT translation.foreign_key,JSON_OBJECTAGG(translation.locale, translation.label) AS labels
    FROM pim_catalog_attribute_translation AS translation
    GROUP BY translation.foreign_key
),
options_count AS (
    SELECT attribute_option.attribute_id, count(*) AS total
    FROM pim_catalog_attribute_option AS attribute_option
    GROUP BY attribute_option.attribute_id
)
SELECT
    attribute.code,
    attribute_labels.labels AS labels,
    options_count.total AS count
FROM pim_catalog_attribute AS attribute
     LEFT JOIN attribute_labels ON attribute.id = attribute_labels.foreign_key
     LEFT JOIN options_count ON attribute.id = options_count.attribute_id
WHERE attribute_type IN ('pim_catalog_simpleselect', 'pim_catalog_multiselect')
$querySearch
GROUP BY attribute.code, attribute_labels.labels, options_count.total
ORDER BY code
$queryLimit $queryOffset
;
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => $size,
                'offset' => $offset,
                'search' => '%' . $search . '%',
                'locale' => '$.' . $locale,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'search' => \PDO::PARAM_STR,
                'locale' => \PDO::PARAM_STR,
            ],
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($rawResults as $rawResult) {

            $rawLabels = !empty($rawResult['labels']) ? json_decode($rawResult['labels'], true) : [];
            $indexedResults[$rawResult['code']]['labels'] = $rawLabels;
            $indexedResults[$rawResult['code']]['options_count'] = (int) $rawResult['count'];
        }

        return $indexedResults;
    }
}

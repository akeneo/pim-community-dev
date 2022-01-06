<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationsByAttribute;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetOptionsCountAndTranslationsByAttribute implements GetOptionsCountAndTranslationsByAttribute
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    //todo create really precise PHPDoc for return type
    public function fromAttributesCode(string $search, int $size = null, int $page = null): string
    {
        $queryLimit = '';
        $queryOffset = '';
        $offset = 0;
        if (!is_null($size)) {
            $queryLimit = 'LIMIT :limit';
            if (!is_null($page) && $page > 1) {
                $queryOffset = 'OFFSET :offset';
                $offset = \abs($page - 1) * $size;
            }
        }

        $query = <<<SQL
WITH attribute_labels AS (
    SELECT DISTINCT translation.foreign_key,JSON_OBJECTAGG(translation.locale, translation.label) as labels
    FROM pim_catalog_attribute_translation as translation
    GROUP BY translation.foreign_key
),
options_count AS (
    SELECT attribute_option.attribute_id, count(*) as total
    FROM pim_catalog_attribute_option as attribute_option
    GROUP BY attribute_option.attribute_id
)
SELECT
    attribute.code,
    JSON_ARRAYAGG(attribute_labels.labels) as labels,
    options_count.total as count
FROM pim_catalog_attribute as attribute
     LEFT JOIN attribute_labels ON attribute.id = attribute_labels.foreign_key
     LEFT JOIN options_count ON attribute.id = options_count.attribute_id
WHERE attribute_type IN ('pim_catalog_simpleselect', 'pim_catalog_multiselect')
GROUP BY attribute.code, attribute_labels.labels, options_count.total
$queryLimit $queryOffset
;
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => $size,
                'offset' => $offset,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($rawResults as $rawResult) {
            $rawLabels = json_decode($rawResult['labels'], true);
            $indexedResults[$rawResult['code']]['labels'] = $rawLabels[0]; //todo better than [0]
            $indexedResults[$rawResult['code']]['options_count'] = (int) $rawResult['count'];
        }

        return json_encode($indexedResults);
    }
}

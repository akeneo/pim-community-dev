<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationByAttribute;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetOptionsCountAndTranslationByAttribute implements GetOptionsCountAndTranslationByAttribute
{
    private const MAX_PAGE_SIZE = 20;

    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array example:
     *      [
     *          ['code' => 'attribute1', 'label' => 'The Label', 'options_count' => 5],
     *          ...
     *      ]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(string $locale, int $page = 1, int $limit = self::MAX_PAGE_SIZE, string $search = null): array
    {
        $querySearch = $search ? 'AND LOWER(COALESCE(t.label, a.code)) like :search' : '';

        $query = <<<SQL
        SELECT a.code, COALESCE(t.label, a.code) AS label, count(o.id) as options_count
        FROM pim_catalog_attribute a
            LEFT JOIN pim_catalog_attribute_option o ON o.attribute_id = a.id
            LEFT JOIN pim_catalog_attribute_translation t ON t.foreign_key = a.id
                AND t.locale = :locale
        WHERE attribute_type IN ('pim_catalog_simpleselect', 'pim_catalog_multiselect')
        $querySearch
        GROUP BY t.label, a.code
        ORDER BY t.label, a.code
        LIMIT :limit OFFSET :offset
        SQL;

        $offset = \abs($page - 1) * $limit;

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => $limit,
                'offset' => $offset,
                'search' => strtolower($search ?? '') . '%',
                'locale' => $locale,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'search' => \PDO::PARAM_STR,
                'locale' => \PDO::PARAM_STR,
            ],
        )->fetchAllAssociative();

        return array_map(
            fn (array $result): array => [
                'code' => $result['code'],
                'label' => $result['label'],
                'options_count' => (int) $result['options_count'],
            ],
            $rawResults
        );
    }
}

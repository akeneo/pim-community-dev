<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
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
     *          ['code' => 'attribute2', 'label' => null, 'options_count' => 2],
     *          ...
     *      ]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(string $localeCode, int $limit = self::MAX_PAGE_SIZE, int $offset = 0, ?string $search = null): array
    {
        $query = <<<SQL
        SELECT a.code, t.label, COUNT(o.id) AS options_count
        FROM pim_catalog_attribute a
            LEFT JOIN pim_catalog_attribute_option o ON o.attribute_id = a.id
            LEFT JOIN pim_catalog_attribute_translation t ON t.foreign_key = a.id AND t.locale = :locale
        WHERE a.attribute_type IN (:select_attribute_types)
        AND COALESCE(t.label, a.code) LIKE :search
        GROUP BY a.code, t.label
        ORDER BY COALESCE(t.label, a.code)
        LIMIT :limit OFFSET :offset
        SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => (int) $limit,
                'offset' => (int) $offset,
                'search' => \sprintf('%%%s%%', $search ?? ''),
                'select_attribute_types' => [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::OPTION_MULTI_SELECT],
                'locale' => $localeCode,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'search' => \PDO::PARAM_STR,
                'select_attribute_types' => Connection::PARAM_STR_ARRAY,
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

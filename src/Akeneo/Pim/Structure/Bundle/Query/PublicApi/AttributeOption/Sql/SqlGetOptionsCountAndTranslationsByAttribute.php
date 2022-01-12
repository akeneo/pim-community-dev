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
    public function search(string $locale, int $limit = self::MAX_PAGE_SIZE, int $page = 1, string $search = null): array
    {
        $offset = \abs($page - 1) * $limit;

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

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => $limit,
                'offset' => $offset,
                'search' => strtolower($search) . '%',
                'locale' => '$.' . $locale,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'search' => \PDO::PARAM_STR,
                'locale' => \PDO::PARAM_STR,
            ],
        )->fetchAllAssociative();

        return $rawResults;
    }
}

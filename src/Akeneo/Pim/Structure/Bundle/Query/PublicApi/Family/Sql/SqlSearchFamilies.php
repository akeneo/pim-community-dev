<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\SearchFamiliesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\SearchFamiliesParameters;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\SearchFamiliesResult;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlSearchFamilies implements SearchFamiliesInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function search(
        SearchFamiliesParameters $searchParameters
    ): SearchFamiliesResult {
        return new SearchFamiliesResult(
            $this->getFamilies($searchParameters),
            $this->getMatchesCount($searchParameters),
        );
    }

    /**
     * @return Family[]
     */
    private function getFamilies(
        SearchFamiliesParameters $searchParameters
    ): array {
        $searchLanguageCondition = null !== $searchParameters->getSearchLanguage() ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $searchParameters->getIncludeCodes() ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($searchParameters->getExcludeCodes()) ? 'AND code NOT IN (:exclude_codes)' : '';
        $limit = null !== $searchParameters->getLimit() ? 'LIMIT :limit' : '';
        $offset = null !== $searchParameters->getOffset() ? 'OFFSET :offset' : '';

        $sql = <<<SQL
WITH filtered_family_codes AS (
    SELECT DISTINCT family.id, family.code
    FROM pim_catalog_family family
    LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLanguageCondition
    WHERE (family.code LIKE :search OR translation.label LIKE :search)
        $includeCondition
        $excludeCondition
    ORDER BY code
    $limit
    $offset
)
SELECT filtered_family_codes.code, labels
FROM filtered_family_codes
LEFT JOIN (
    SELECT foreign_key, JSON_OBJECTAGG(locale, label) AS labels
    FROM pim_catalog_family_translation
    GROUP BY foreign_key
) AS translation
ON filtered_family_codes.id = translation.foreign_key
ORDER BY code
SQL;

        $families = $this->connection->executeQuery($sql, [
            'search' => sprintf('%%%s%%', $searchParameters->getSearch()),
            'locale_code' => $searchParameters->getSearchLanguage(),
            'include_codes' => $searchParameters->getIncludeCodes(),
            'exclude_codes' => $searchParameters->getExcludeCodes(),
            'limit' => $searchParameters->getLimit(),
            'offset' => $searchParameters->getOffset(),
        ], [
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ])->fetchAllAssociative();

        return array_map(
            static fn (array $rawFamily) => new Family(
                $rawFamily['code'],
                null !== $rawFamily['labels'] ? json_decode($rawFamily['labels'], true) : [],
            ),
            $families,
        );
    }

    private function getMatchesCount(
        SearchFamiliesParameters $searchParameters
    ): int {
        $searchLanguageCondition = null !== $searchParameters->getSearchLanguage() ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $searchParameters->getIncludeCodes() ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($searchParameters->getExcludeCodes()) ? 'AND code NOT IN (:exclude_codes)' : '';

        $sql = <<<SQL
SELECT COUNT(DISTINCT family.id)
FROM pim_catalog_family family
LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLanguageCondition
WHERE (family.code LIKE :search OR translation.label LIKE :search)
    $includeCondition
    $excludeCondition
SQL;

        $matchesCount = $this->connection->executeQuery($sql, [
            'search' => sprintf('%%%s%%', $searchParameters->getSearch() ?? ''),
            'locale_code' => $searchParameters->getSearchLanguage(),
            'include_codes' => $searchParameters->getIncludeCodes(),
            'exclude_codes' => $searchParameters->getExcludeCodes(),
        ], [
            'attribute_code' => \PDO::PARAM_STR,
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
        ])->fetchOne();

        return (int) $matchesCount;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsResult;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlSearchAttributeOptions implements SearchAttributeOptionsInterface
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function search(
        string $attributeCode,
        SearchAttributeOptionsParameters $searchParameters
    ): SearchAttributeOptionsResult {
        return new SearchAttributeOptionsResult(
            $this->getAttributeOptions($attributeCode, $searchParameters),
            $this->getMatchesCount($attributeCode, $searchParameters),
        );
    }

    /**
     * @return AttributeOption[]
     */
    private function getAttributeOptions(
        string $attributeCode,
        SearchAttributeOptionsParameters $searchParameters
    ): array {
        $localeCondition = null !== $searchParameters->getLocale() ? 'AND option_value.locale_code = :locale_code' : '';
        $includeCondition = null !== $searchParameters->getIncludeCodes() ? 'AND option.code IN (:include_codes)' : '';
        $excludeCondition = !empty($searchParameters->getExcludeCodes()) ? 'AND option.code NOT IN (:exclude_codes)' : '';
        $order = $this->isAttributeAutoSorted($attributeCode) ? 'code' : 'sort_order, code';
        $limit = null !== $searchParameters->getLimit() ? 'LIMIT :limit' : '';
        $offset = null !== $searchParameters->getOffset() ? 'OFFSET :offset' : '';

        $sql = <<<SQL
WITH filtered_option_codes AS (
    SELECT DISTINCT option.id, option.code, option.sort_order
    FROM pim_catalog_attribute_option `option`
    INNER JOIN pim_catalog_attribute `attribute` ON option.attribute_id = attribute.id
    LEFT JOIN pim_catalog_attribute_option_value `option_value` ON option.id = option_value.option_id $localeCondition
    WHERE attribute.code = :attribute_code
        AND (option.code LIKE :search OR option_value.value LIKE :search)
        $includeCondition
        $excludeCondition
    ORDER BY $order
    $limit
    $offset
)
SELECT filtered_option_codes.code, labels
FROM filtered_option_codes
LEFT JOIN (
    SELECT option_value.option_id, JSON_OBJECTAGG(option_value.locale_code, option_value.value) AS labels
    FROM pim_catalog_attribute_option_value `option_value`
    GROUP BY option_value.option_id
) AS label
ON filtered_option_codes.id = label.option_id
ORDER BY $order
SQL;

        $attributeOptions = $this->connection->executeQuery($sql, [
            'attribute_code' => $attributeCode,
            'search' => sprintf('%%%s%%', $searchParameters->getSearch()),
            'locale_code' => $searchParameters->getLocale(),
            'include_codes' => $searchParameters->getIncludeCodes(),
            'exclude_codes' => $searchParameters->getExcludeCodes(),
            'limit' => $searchParameters->getLimit(),
            'offset' => $searchParameters->getOffset(),
        ], [
            'attribute_code' => \PDO::PARAM_STR,
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ])->fetchAllAssociative();

        return array_map(
            static fn (array $attributeOption) => new AttributeOption(
                $attributeOption['code'],
                null !== $attributeOption['labels'] ? json_decode($attributeOption['labels'], true) : [],
            ),
            $attributeOptions,
        );
    }

    private function getMatchesCount(
        string $attributeCode,
        SearchAttributeOptionsParameters $searchParameters
    ): int {
        $localeCondition = null !== $searchParameters->getLocale() ? 'AND option_value.locale_code = :locale_code' : '';
        $includeCondition = null !== $searchParameters->getIncludeCodes() ? 'AND option.code IN (:include_codes)' : '';
        $excludeCondition = !empty($searchParameters->getExcludeCodes()) ? 'AND option.code NOT IN (:exclude_codes)' : '';

        $sql = <<<SQL
SELECT COUNT(DISTINCT option.id)
FROM pim_catalog_attribute_option `option`
INNER JOIN pim_catalog_attribute `attribute` ON option.attribute_id = attribute.id
LEFT JOIN pim_catalog_attribute_option_value `option_value` ON option.id = option_value.option_id $localeCondition
WHERE attribute.code = :attribute_code
    AND (option.code LIKE :search OR option_value.value LIKE :search)
    $includeCondition
    $excludeCondition
SQL;

        $matchesCount = $this->connection->executeQuery($sql, [
            'attribute_code' => $attributeCode,
            'search' => sprintf('%%%s%%', $searchParameters->getSearch() ?? ''),
            'locale_code' => $searchParameters->getLocale(),
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

    private function isAttributeAutoSorted(string $attributeCode): bool
    {
        $attributeProperties = unserialize($this->connection->executeQuery(
            'SELECT properties from pim_catalog_attribute attribute WHERE attribute.code = :attribute_code',
            ['attribute_code' => $attributeCode],
        )->fetchOne());

        return $attributeProperties['auto_option_sorting'] ?? false;
    }
}

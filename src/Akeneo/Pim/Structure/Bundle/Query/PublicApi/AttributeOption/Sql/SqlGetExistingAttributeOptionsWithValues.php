<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function fromAttributeCodeAndOptionCodes(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $queryParams = [];
        $queryStringParams = [];
        foreach ($keys as $key) {
            $queryParams[] = $this->getAttributeCodeFromKey($key);
            $queryParams[] = $this->getOptionCodeFromKey($key);
            $queryStringParams[] = "(?, ?)";
        }

        $query = <<<SQL
WITH active_locales as (select code from pim_catalog_locale where is_activated is true)
SELECT
    CONCAT(attribute.code, '.', attribute_option.code) as option_key,
    JSON_OBJECTAGG(active_locales.code, option_value.value) as labels
FROM active_locales
    CROSS JOIN pim_catalog_attribute attribute
    INNER JOIN pim_catalog_attribute_option attribute_option ON attribute.id = attribute_option.attribute_id
    LEFT JOIN pim_catalog_attribute_option_value option_value ON attribute_option.id = option_value.option_id
                                                              AND active_locales.code = option_value.locale_code
WHERE (attribute.code, attribute_option.code) IN (%s)
GROUP BY attribute.code, attribute_option.code
SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($rawResults as $rawResult) {
            $indexedResults[$rawResult['option_key']] = json_decode($rawResult['labels'], true);
        }

        return $indexedResults;
    }

    private function getAttributeCodeFromKey(string $key): string
    {
        $chunks = explode('.', $key);

        return $chunks[0];
    }

    private function getOptionCodeFromKey(string $key): string
    {
        $chunks = explode('.', $key);

        return $chunks[1];
    }
}

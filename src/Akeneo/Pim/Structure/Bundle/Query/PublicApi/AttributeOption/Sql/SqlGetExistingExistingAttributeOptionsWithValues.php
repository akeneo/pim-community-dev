<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetExistingExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
{
    /** @var Connection */
    private $connection;

    /** @var LRUCache */
    private $cache;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->cache = new LRUCache(10000);
    }

    /**
     * {@inheritDoc}
     */
    public function fromAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes): array
    {
        if (empty($optionCodes)) {
            return [];
        }

        $keys = array_map(function ($optionCode) use ($attributeCode) {
            return $this->buildKey($attributeCode, $optionCode);
        }, $optionCodes);

        $resultsFromCache = $this->cache->getForKeys($keys, \Closure::fromCallable([$this, 'getFromKeys']));

        $results = [];
        $attributeCode = '';
        $optionCode = '';
        foreach ($resultsFromCache as $key => $result) {
            $this->extractFromKey($key, $attributeCode, $optionCode);
            $results[$optionCode] = $result;
        }

        return $results;
    }

    /**
     * @param string[] $keys
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getFromKeys(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $queryParams = [];
        $queryStringParams = [];
        $optionCode = '';
        $attributeCode = '';
        foreach ($keys as $key) {
            $this->extractFromKey($key, $attributeCode, $optionCode);
            $queryParams[] = $attributeCode;
            $queryParams[] = $optionCode;
            $queryStringParams[] = "(?, ?)";
        }

        $query = <<<SQL
SELECT
    attribute.code        AS attribute_code,
    attribute_option.code AS option_code,
    option_value.locale_code,
    option_value.value
FROM pim_catalog_attribute attribute
    INNER JOIN pim_catalog_attribute_option attribute_option ON attribute.id = attribute_option.attribute_id
    LEFT JOIN pim_catalog_attribute_option_value option_value ON attribute_option.id = option_value.option_id
WHERE (attribute.code, attribute_option.code) IN (%s)
SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetchAll();

        $results = [];
        foreach ($rawResults as $rawResult) {
            $key = $this->buildKey($rawResult['attribute_code'], $rawResult['option_code']);
            if (!isset($results[$key])) {
                $results[$key] = [];
            }

            $localeCode = $rawResult['locale_code'];
            if (null !== $localeCode) {
                $results[$key][$localeCode] = $rawResult['value'];
            }
        }

        return $results;
    }

    private function buildKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s|%s', $attributeCode, $optionCode);
    }

    private function extractFromKey(string $key, string &$attributeCode, string &$optionCode): void
    {
        $chunks = explode('|', $key);

        $attributeCode = $chunks[0];
        $optionCode = $chunks[1];
    }
}

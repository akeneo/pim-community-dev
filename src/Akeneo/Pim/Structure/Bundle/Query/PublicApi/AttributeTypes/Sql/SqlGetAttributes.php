<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeTypes\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetAttributes implements GetAttributes
{
    /** @var Connection */
    private $connection;

    /** @var array|Attribute[] */
    private $cache = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $cachedAttributeCodes = array_keys($this->cache);
        $attributeCodesToQuery = array_diff($attributeCodes, $cachedAttributeCodes);

        $attributesFromDb = $this->fetchAttributesFromDb($attributeCodesToQuery);

        $this->cache = array_replace($this->cache, $attributesFromDb);

        //It is to avoid errors on removed attributes
        $attributeCodesToKeep = array_intersect(array_keys($this->cache), $attributeCodes);

        $result = [];
        foreach ($attributeCodesToKeep as $attributeCode) {
            $result[] = $this->cache[(string)$attributeCode];
        }

        return $result;
    }

    private function fetchAttributesFromDb(array $attributeCodes): array
    {
        if ($attributeCodes === []) {
            return [];
        }

        $query = <<<SQL
        SELECT code, attribute_type, properties, is_scopable, is_localizable, metric_family, decimals_allowed
        FROM pim_catalog_attribute
        WHERE code IN (:attributeCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $codesToAttribute = [];
        foreach ($rawResults as $attribute) {
            $properties = unserialize($attribute['properties']);

            $codesToAttribute[(string)$attribute['code']] = new Attribute(
                $attribute['code'],
                $attribute['attribute_type'],
                $properties,
                boolval($attribute['is_localizable']),
                boolval($attribute['is_scopable']),
                $attribute['metric_family'],
                boolval($attribute['decimals_allowed'])
            );
        }

        return $codesToAttribute;
    }
}

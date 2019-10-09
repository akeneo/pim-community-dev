<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

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

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<<SQL
SELECT code, attribute_type, properties, is_scopable, is_localizable, metric_family, decimals_allowed, backend_type
FROM pim_catalog_attribute
WHERE code IN (:attributeCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $attributes = [];

        foreach ($rawResults as $rawAttribute) {
            $properties = unserialize($rawAttribute['properties']);

            $attributes[$rawAttribute['code']] = new Attribute(
                $rawAttribute['code'],
                $rawAttribute['attribute_type'],
                $properties,
                boolval($rawAttribute['is_localizable']),
                boolval($rawAttribute['is_scopable']),
                $rawAttribute['metric_family'],
                boolval($rawAttribute['decimals_allowed']),
                $rawAttribute['backend_type']
            );
        }

        return array_replace(array_fill_keys($attributeCodes, null), $attributes);
    }

    public function forCode(string $attributeCode): ?Attribute
    {
        $forCodes = $this->forCodes([$attributeCode]);

        if ([] === $forCodes) {
            return null;
        }

        return array_pop($forCodes);
    }
}

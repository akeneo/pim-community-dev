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
WITH locale_specific_codes AS (
    SELECT attribute_locale.attribute_id, JSON_ARRAYAGG(locale.code) AS locale_codes
    FROM pim_catalog_attribute_locale attribute_locale
    INNER JOIN pim_catalog_locale locale ON attribute_locale.locale_id = locale.id
    GROUP BY attribute_locale.attribute_id
)
SELECT attribute.code,
       attribute.attribute_type,
       attribute.properties,
       attribute.is_scopable,
       attribute.is_localizable,
       attribute.metric_family,
       attribute.decimals_allowed,
       attribute.backend_type,
       COALESCE(locale_codes, JSON_ARRAY()) AS available_locale_codes
FROM pim_catalog_attribute attribute
    LEFT JOIN locale_specific_codes on attribute.id = attribute_id    
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
                $rawAttribute['backend_type'],
                json_decode($rawAttribute['available_locale_codes'])
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

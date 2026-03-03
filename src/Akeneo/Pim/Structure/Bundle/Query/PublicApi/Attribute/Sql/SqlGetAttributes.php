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
),
translation as (
    SELECT attribute.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
    FROM pim_catalog_attribute as attribute
        JOIN pim_catalog_attribute_translation translation ON translation.foreign_key = attribute.id
    WHERE translation.label IS NOT NULL
        AND translation.label != ''
        AND attribute.code IN (:attributeCodes)
    GROUP BY attribute.code
)
SELECT attribute.code,
       attribute.attribute_type,
       attribute.properties,
       attribute.is_scopable,
       attribute.is_localizable,
       attribute.metric_family,
       attribute.default_metric_unit,
       attribute.decimals_allowed,
       attribute.backend_type,
       attribute.useable_as_grid_filter,
       attribute.main_identifier,
       COALESCE(locale_codes, JSON_ARRAY()) AS available_locale_codes,
       translation.translations
FROM pim_catalog_attribute attribute
    LEFT JOIN locale_specific_codes on attribute.id = attribute_id
    LEFT JOIN translation on attribute.code = translation.code
WHERE attribute.code IN (:attributeCodes)
GROUP BY attribute.id
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $attributes = [];

        foreach ($rawResults as $rawAttribute) {
            $properties = unserialize($rawAttribute['properties']);

            $translations = $rawAttribute['translations'] !== null ? json_decode($rawAttribute['translations'], true) : [];

            $attributes[$rawAttribute['code']] = new Attribute(
                $rawAttribute['code'],
                $rawAttribute['attribute_type'],
                $properties,
                boolval($rawAttribute['is_localizable']),
                boolval($rawAttribute['is_scopable']),
                $rawAttribute['metric_family'],
                $rawAttribute['default_metric_unit'],
                boolval($rawAttribute['decimals_allowed']),
                $rawAttribute['backend_type'],
                json_decode($rawAttribute['available_locale_codes']),
                boolval($rawAttribute['useable_as_grid_filter']),
                $translations,
                boolval($rawAttribute['main_identifier']),
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

    public function forType(string $attributeType): array
    {
        $query = <<<SQL
            SELECT attribute.code
            FROM pim_catalog_attribute attribute
            WHERE attribute.attribute_type = (:attribute_type)
        SQL;

        $codes = $this->connection->executeQuery(
            $query,
            ['attribute_type' => $attributeType]
        )->fetchFirstColumn();

        return $this->forCodes($codes);
    }
}

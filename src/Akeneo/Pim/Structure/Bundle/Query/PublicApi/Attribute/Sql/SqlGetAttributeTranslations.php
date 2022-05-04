<?php

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Doctrine\DBAL\Connection;

class SqlGetAttributeTranslations implements GetAttributeTranslations
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inerhitDoc
     */
    public function byAttributeCodesAndLocale(array $attributeCodes, string $locale): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<<SQL
SELECT code, label
FROM pim_catalog_attribute_translation at
LEFT JOIN pim_catalog_attribute a ON a.id = at.foreign_key
WHERE locale = :locale
AND code IN (:attributeCodes);
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            [
                'locale' => $locale,
                'attributeCodes' => $attributeCodes
            ],
            [
                'attributeCodes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAllAssociative();

        $attributeTranslations = [];
        foreach ($rows as $attribute) {
            $attributeTranslations[$attribute['code']] = $attribute['label'];
        }

        return $attributeTranslations;
    }

    /**
     * @inerhitDoc
     */
    public function byAttributeCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<<SQL
            SELECT
                code,
                JSON_OBJECTAGG(attribute_translation.locale, attribute_translation.label) as labels
            FROM pim_catalog_attribute_translation attribute_translation
            LEFT JOIN pim_catalog_attribute attribute ON attribute.id = attribute_translation.foreign_key
            WHERE attribute.code IN (:attributeCodes)
            GROUP BY attribute.code;
        SQL;

        $rows = $this->connection->executeQuery(
            $query,
            [
                'attributeCodes' => $attributeCodes
            ],
            [
                'attributeCodes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAllAssociative();

        $attributeTranslations = [];
        foreach ($rows as $attribute) {
            $attributeTranslations[$attribute['code']] = json_decode($attribute['labels'], true);
        }

        return $attributeTranslations;
    }
}

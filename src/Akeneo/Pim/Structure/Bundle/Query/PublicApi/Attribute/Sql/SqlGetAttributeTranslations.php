<?php

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Doctrine\DBAL\Connection;

class SqlGetAttributeTranslations implements GetAttributeTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byAttributeCodesAndLocale(array $attributeCodes, string $locale)
    {
        if (empty($attributeCodes)) {
           return [];
        }

        $query = <<<SQL
SELECT code, label
FROM akeneo_pim.pim_catalog_attribute a
LEFT JOIN akeneo_pim.pim_catalog_attribute_translation at ON a.id = at.foreign_key
WHERE locale = :locale
AND code IN (:attributeCodes);
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'locale' => $locale,
                'attributeCodes' => $attributeCodes
            ],
            [
                'attributeCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $attributeTranslations = array_fill_keys($attributeCodes, null);
        $results = $statement->fetchAll();
        foreach ($results as $attribute) {
            $attributeTranslations[$attribute['code']] = $attribute['label'];
        }

        return $attributeTranslations;
    }
}

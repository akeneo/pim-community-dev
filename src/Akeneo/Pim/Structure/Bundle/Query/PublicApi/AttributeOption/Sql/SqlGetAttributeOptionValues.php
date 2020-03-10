<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetAttributeOptionValues;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAttributeOptionValues implements GetAttributeOptionValues
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
    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        if (empty($optionCodesIndexedByAttributeCodes)) {
            return [];
        }

        $queryParams = [];
        $queryStringParams = [];

        foreach ($optionCodesIndexedByAttributeCodes as $attributeCode => $optionCodes) {
            foreach ($optionCodes as $optionCode) {
                $queryParams[] = $attributeCode;
                $queryParams[] = $optionCode;
                $queryStringParams[] = "(?, ?)";
            }
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
            if (!isset($results[$rawResult['attribute_code']][$rawResult['option_code']])) {
                $results[$rawResult['attribute_code']][$rawResult['option_code']] = [];
            }

            if (null !== $rawResult['locale_code']) {
                $results[$rawResult['attribute_code']][$rawResult['option_code']][$rawResult['locale_code']] = $rawResult['value'];
            }
        }

        return $results;
    }
}

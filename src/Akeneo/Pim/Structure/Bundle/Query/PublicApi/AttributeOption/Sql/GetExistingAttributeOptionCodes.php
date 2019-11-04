<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes as GetExistingAttributeOptionCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingAttributeOptionCodes implements GetExistingAttributeOptionCodesInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

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
        SELECT pim_catalog_attribute.code as attribute_code, JSON_ARRAYAGG(pim_catalog_attribute_option.code) as option_codes
        FROM pim_catalog_attribute_option INNER JOIN pim_catalog_attribute ON pim_catalog_attribute_option.attribute_id = pim_catalog_attribute.id
        WHERE (pim_catalog_attribute.code, pim_catalog_attribute_option.code) IN (%s)
        GROUP BY pim_catalog_attribute.code
SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetchAll();

        $results =  array_reduce($rawResults, function (array $results, array  $item): array {
            $results[$item['attribute_code']] = json_decode($item['option_codes'], true);

            return $results;
        }, []);

        return $results;
    }
}

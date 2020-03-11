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
final class SqlGetExistingExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
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
    public function fromAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes): array
    {

        // @todo: add LRU cache!

        if (empty($optionCodes)) {
            return [];
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
WHERE attribute.code = :attributeCode AND attribute_option.code IN (:optionCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'attributeCode' => $attributeCode,
                'optionCodes' => $optionCodes,
            ],
            ['optionCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $results = [];
        foreach ($rawResults as $rawResult) {
            $optionCode = $rawResult['option_code'];
            $localeCode = $rawResult['locale_code'];
            if (!isset($results[$optionCode])) {
                $results[$optionCode] = [];
            }

            if (null !== $localeCode) {
                $results[$optionCode][$localeCode] = $rawResult['value'];
            }
        }

        return $results;
    }
}

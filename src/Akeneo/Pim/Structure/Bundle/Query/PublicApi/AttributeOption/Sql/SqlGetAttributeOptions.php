<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetAttributeOptions;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetAttributeOptions implements GetAttributeOptions
{
    private const BATCH_QUERY_SIZE = 1000;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAttributeCode(string $attributeCode): iterable
    {
        $sql = <<<SQL
        WITH active_locales as (select code from pim_catalog_locale where is_activated is true)
        SELECT
            attribute.code AS attributeCode, attribute_option.code as attributeOptionCode, attribute_option.id as attributeOptionId,
            JSON_OBJECTAGG(active_locales.code, option_value.value) as labels
        FROM active_locales
                 CROSS JOIN pim_catalog_attribute attribute
                 INNER JOIN pim_catalog_attribute_option attribute_option ON attribute.id = attribute_option.attribute_id
                 LEFT JOIN pim_catalog_attribute_option_value option_value ON attribute_option.id = option_value.option_id
            AND active_locales.code = option_value.locale_code
        WHERE attribute.code = :attributeCode AND attribute_option.id > :searchAfterId
        GROUP BY attribute.code, attribute_option.id
        ORDER BY attribute_option.id
        LIMIT :limit
        SQL;

        $searchAfterId = 0;
        do {
            $results = $this->connection->executeQuery(
                $sql,
                [
                    'attributeCode' => $attributeCode,
                    'searchAfterId' => $searchAfterId,
                    'limit' => self::BATCH_QUERY_SIZE,
                ],
                ['limit' => \PDO::PARAM_INT]
            )->fetchAllAssociative();
            foreach ($results as $result) {
                yield new AttributeOption(
                    $result['attributeOptionCode'],
                    \array_filter(\json_decode($result['labels'], true), fn ($label): bool => null !== $label)
                );
                $searchAfterId = $result['attributeOptionId'];
            }
        } while (\count($results) === self::BATCH_QUERY_SIZE);
    }
}

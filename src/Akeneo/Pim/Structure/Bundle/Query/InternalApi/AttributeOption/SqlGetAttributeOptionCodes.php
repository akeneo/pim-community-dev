<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAttributeOptionCodes;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetAttributeOptionCodes implements GetAttributeOptionCodes
{
    private const BATCH_QUERY_SIZE = 1000;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAttributeCode(string $attributeCode): \Iterator
    {
        $sql = <<<SQL
        SELECT ao.id, ao.code
        FROM pim_catalog_attribute_option ao
            JOIN pim_catalog_attribute a ON a.id = ao.attribute_id
        WHERE a.code = :attributeCode AND ao.id > :searchAfterId
        ORDER BY ao.id
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
                yield $result['code'];
                $searchAfterId = $result['id'];
            }
        } while (count($results) === self::BATCH_QUERY_SIZE);
    }
}

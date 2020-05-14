<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;
use Doctrine\DBAL\Connection;

final class SqlGetUniqueAttributeCodes implements GetUniqueAttributeCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromAttributeCodes(array $attributeCodes): array
    {
        $query = <<<SQL
SELECT attribute.code
FROM akeneo_pim.pim_catalog_attribute attribute
WHERE attribute.is_unique = 1
AND attribute.code IN (:attributeCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['attributeCodes' =>  $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $uniqueAttributeCodes = [];

        foreach($rawResults as $rawAttribute) {
            $uniqueAttributeCodes[] = $rawAttribute['code'];
        }

        return $uniqueAttributeCodes;
    }
}

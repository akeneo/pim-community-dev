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

    public function fromFamilyCode(string $familyCode): array
    {
        $query = <<<SQL
SELECT a.code
FROM akeneo_pim.pim_catalog_attribute a
    LEFT JOIN akeneo_pim.pim_catalog_family_attribute fa ON fa.attribute_id = a .id
    LEFT JOIN akeneo_pim.pim_catalog_family f ON f.id = fa.family_id
WHERE a.is_unique = 1
AND f.code = (:familyCode)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['familyCode' =>  $familyCode]
        )->fetchAll();

        $uniqueAttributeCodes = [];

        foreach($rawResults as $rawAttribute) {
            $uniqueAttributeCodes[] = $rawAttribute['code'];
        }

        return $uniqueAttributeCodes;
    }
}

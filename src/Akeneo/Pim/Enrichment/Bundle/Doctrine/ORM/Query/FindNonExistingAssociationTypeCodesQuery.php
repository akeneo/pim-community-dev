<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingAssociationTypeCodesQueryInterface;
use Doctrine\DBAL\Connection;

class FindNonExistingAssociationTypeCodesQuery implements FindNonExistingAssociationTypeCodesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        $query = <<<SQL
        SELECT code FROM pim_catalog_association_type WHERE code IN (:codes)
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['codes' => $codes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN);

        $nonExistingCodes = array_values(array_diff($codes, $results));

        return $nonExistingCodes;
    }
}

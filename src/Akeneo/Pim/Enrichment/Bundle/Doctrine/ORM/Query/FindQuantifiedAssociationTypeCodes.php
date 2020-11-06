<?php


namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;

class FindQuantifiedAssociationTypeCodes implements FindQuantifiedAssociationTypeCodesInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(\Doctrine\DBAL\Driver\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): array
    {
        $query = <<<SQL
        SELECT code FROM pim_catalog_association_type WHERE is_quantified = true
SQL;

        return $this->connection->executeQuery($query)->fetchAll(\PDO::FETCH_COLUMN);
    }
}

<?php


namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Doctrine\DBAL\Connection;

class FindQuantifiedAssociationTypeCodes implements FindQuantifiedAssociationTypeCodesInterface, CachedQueryInterface
{
    private Connection $connection;

    private ?array $cachedResult = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): array
    {
        if (null === $this->cachedResult) {
            $this->cachedResult = $this->fetch();
        }

        return $this->cachedResult;
    }

    public function clear(): void
    {
        $this->cachedResult = null;
    }

    protected function fetch(): array
    {
        $query = <<<SQL
        SELECT code FROM pim_catalog_association_type WHERE is_quantified = true
SQL;

        return $this->connection->executeQuery($query)->fetchAll(\PDO::FETCH_COLUMN);
    }
}

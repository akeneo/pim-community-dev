<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillJson implements MigrateToUuidStep
{
    public function __construct(private Connection $connection)
    {
    }

    public function getMissingCount(OutputInterface $output): int
    {
        $sql = "
SELECT COUNT(1)
FROM pim_catalog_product 
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
  AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid');";

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    public function addMissing(OutputInterface $output): void
    {
        $sql = "
SELECT id, quantified_associations
FROM pim_catalog_product 
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
  AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid') LIMIT 1;";

    }
}

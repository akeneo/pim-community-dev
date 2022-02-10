<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillJson implements MigrateToUuidStep
{
    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Adds product_uuid field in JSON objects';
    }

    public function getMissingCount(): int
    {
        $sql = "
SELECT COUNT(1)
FROM pim_catalog_product
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid');";

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        $output->writeln('TODO');

        $sql = '
SELECT *,
       JSON_EXTRACT(
    pim_catalog_product.quantified_associations,
    CONCAT("$[", association_code, "]")
) assocation_values
       
       FROM (
    SELECT id AS product_id, 
    association_codes.association_code
    FROM pim_catalog_product,
    JSON_TABLE(
        JSON_KEYS(pim_catalog_product.quantified_associations),
        "$[*]"
        COLUMNS (
            association_code varchar(100) PATH "$")
        ) association_codes
) toto

';
    }

}


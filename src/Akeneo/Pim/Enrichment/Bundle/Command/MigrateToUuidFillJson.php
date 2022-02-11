<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillJson implements MigrateToUuidStep
{
    private const BATCH_SIZE = 1000;
    private const TABLE_NAMES = [
        'pim_catalog_product',
        'pim_catalog_product_model'
    ];

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
FROM %s
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid');";

        $count = 0;
        foreach (self::TABLE_NAMES as $tableName) {
            $result = $this->connection->fetchOne(sprintf($sql, $tableName));
            $count += (int) $result;
        }

        return $count;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        foreach (self::TABLE_NAMES as $tableName) {
            $this->addMissingForTable($dryRun, $output, $tableName);
        }
    }

    private function updateAssociations(string $tableName, $productAssociations): void
    {
        // TODO Should be better than that

        foreach ($productAssociations as $productAssociation) {
            $this->connection->executeQuery(sprintf(
                'UPDATE %s SET quantified_associations=\'%s\' WHERE id=%d',
                $tableName,
                \json_encode($productAssociation['quantified_associations']),
                $productAssociation['id']
            ));
        }
    }

    private function getFormerAssociations(string $tableName, $previousProductId = -1): array
    {
        $associationsSql = "SELECT id, quantified_associations
FROM %s
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid')
AND id > :previousProductId
ORDER BY id 
LIMIT :limit";

        $associations = $this->connection->fetchAllAssociative(sprintf($associationsSql, $tableName), [
            'previousProductId' => $previousProductId,
            'limit' => self::BATCH_SIZE
        ], [
            'previousProductId' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);

        $result = [];
        foreach ($associations as $association) {
            $result[] = ['id' => $association['id'], 'quantified_associations' => \json_decode($association['quantified_associations'], true)];
        }

        return $result;
    }

    private function getProductIdToUuidMap(array $productFormerAssociations)
    {
        $productIds = [];
        foreach ($productFormerAssociations as $formerAssociation) {
            foreach ($formerAssociation['quantified_associations'] as $associationValues) {
                foreach ($associationValues['products'] as $associated_product) {
                    $productIds[] = $associated_product['id'];
                }
            }
        }

        $productsSql = "SELECT id, BIN_TO_UUID(uuid) as uuid FROM pim_catalog_product WHERE id IN (:productIds)";
        $products = $this->connection->fetchAllAssociative($productsSql, [
            'productIds' => $productIds,
        ], [
            'productIds' => Connection::PARAM_INT_ARRAY
        ]);

        $result = [];
        foreach ($products as $product) {
            $result[\intval($product['id'])] = $product['uuid'];
        }

        return $result;
    }

    private function addMissingForTable(bool $dryRun, OutputInterface $output, string $tableName): void
    {
        $previousEntityId = -1;
        $associations = $this->getFormerAssociations($tableName, $previousEntityId);
        while (count($associations) > 0) {
            $productIdToUuidMap = $this->getProductIdToUuidMap($associations);

            for ($pi = 0; $pi < count($associations); $pi++) {
                $formerAssociation = $associations[$pi]['quantified_associations'];
                $productId = $associations[$pi]['id'];
                foreach ($formerAssociation as $associationName => $entityAssociations) {
                    for ($i = 0; $i < count($entityAssociations['products']); $i++) {
                        $associatedProductId = $entityAssociations['products'][$i]['id'];
                        if (array_key_exists($associatedProductId, $productIdToUuidMap)) {
                            $associatedProductUuid = $productIdToUuidMap[$associatedProductId];
                            $associations[$pi][$associationName]['products'][$i]['uuid'] = $associatedProductUuid;
                        } else {
                            $output->writeln(sprintf('    <comment>Associated product %d not found for product %d</comment>', $associatedProductId, $productId));
                        }
                    }
                }
                $previousEntityId = $productId;
            }

            $output->writeln(sprintf('    Will update %s entities in %s table', count($associations), $tableName));
            if (!$dryRun) {
                $this->updateAssociations($tableName, $associations);
                $associations = $this->getFormerAssociations($tableName, $previousEntityId);
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $associations = [];
            }
        }
    }
}

/*
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1034, "quantity": 10000}], "product_models": [{"id": 1, "quantity": 1}, {"id": 12, "quantity": 1}]}}' WHERE id=1022;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 10, "quantity": 1}], "product_models": []}}' WHERE id=1111;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1207;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 400000, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1217;
UPDATE pim_catalog_product_model SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1, "quantity": 10}, {"id": 10, "quantity": 1}], "product_models": []}}' WHERE id=1;
*/


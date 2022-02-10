<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillJson implements MigrateToUuidStep
{
    private const BATCH_SIZE = 1000;

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
        $previousProductId = -1;
        $productAssociations = $this->getProductFormerAssociations($previousProductId);
        while (count($productAssociations) > 0) {
            $productIdToUuidMap = $this->getProductIdToUuidMap($productAssociations);

            foreach ($productAssociations as $productId => $formerAssociation) {
                foreach ($formerAssociation as $associationName => $associations) {
                    for ($i = 0; $i < count($associations['products']); $i++) {
                        $associatedProductId = $associations['products'][$i]['id'];
                        if (array_key_exists($associatedProductId, $productIdToUuidMap)) {
                            $associatedProductUuid = $productIdToUuidMap[$associatedProductId];
                            $productAssociations[$productId][$associationName]['products'][$i]['uuid'] = $associatedProductUuid;
                        } else {
                            $output->writeln(sprintf('    <comment>Associated product %d not found for product %d</comment>', $associatedProductId, $productId));
                        }
                    }
                }
                $previousProductId = $productId;
            }

            $output->writeln(sprintf('    Will update %s products', count($productAssociations)));
            if (!$dryRun) {
                $this->updateAssociations($productAssociations);
                $productAssociations = $this->getProductFormerAssociations($previousProductId);
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $productAssociations = [];
            }
        }
    }

    private function updateAssociations($productAssociations): void
    {
        // TODO Should be better than that

        foreach ($productAssociations as $productId => $productAssociation) {
            $this->connection->executeQuery(sprintf('UPDATE pim_catalog_product SET quantified_associations=\'%s\' WHERE id=%d', \json_encode($productAssociation), $productId));
        }
    }

    private function getProductFormerAssociations($previousProductId = -1): array
    {
        $associationsSql = "SELECT id, quantified_associations
FROM pim_catalog_product
WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid')
AND id > :previousProductId
ORDER BY id 
LIMIT :limit";

        $associations = $this->connection->fetchAllAssociative($associationsSql, [
            'previousProductId' => $previousProductId,
            'limit' => self::BATCH_SIZE
        ], [
            'previousProductId' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ]);

        $result = [];
        foreach ($associations as $association) {
            $result[$association['id']] = \json_decode($association['quantified_associations'], true);
        }

        return $result;
    }

    private function getProductIdToUuidMap(array $productFormerAssociations)
    {
        $productIds = [];
        foreach ($productFormerAssociations as $formerAssociation) {
            foreach (array_values($formerAssociation) as $associationValues) {
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
}

/*
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1034, "quantity": 10000}], "product_models": [{"id": 1, "quantity": 1}, {"id": 12, "quantity": 1}]}}' WHERE id=1022;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 10, "quantity": 1}], "product_models": []}}' WHERE id=1111;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1207;
UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 400000, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1217;
*/

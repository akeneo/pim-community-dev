<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  Queries to try this migration:
 *
    UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1034, "quantity": 10000}], "product_models": [{"id": 1, "quantity": 1}, {"id": 12, "quantity": 1}]}}' WHERE id=1022;
    UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 10, "quantity": 1}], "product_models": []}}' WHERE id=1111;
    UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1207;
    UPDATE pim_catalog_product SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 400000, "quantity": 50000}, {"id": 10, "quantity": 1}, {"id": 100, "quantity": 1}], "product_models": []}}' WHERE id=1217;
    UPDATE pim_catalog_product_model SET quantified_associations='{"SOIREEFOOD10": {"products": [{"id": 1, "quantity": 10}, {"id": 10, "quantity": 1}], "product_models": []}}' WHERE id=1;
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidFillJson implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

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

    public function shouldBeExecuted(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS(
                SELECT 1
                FROM {table_name}
                WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
                    AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid')
                LIMIT 1
            ) as missing
        SQL;

        foreach (self::TABLE_NAMES as $tableName) {
            if ((bool) $this->connection->executeQuery(\strtr($sql, ['{table_name}' => $tableName]))->fetchOne()) {
                return true;
            }
        }

        return false;
    }

    public function getMissingCount(): int
    {
        $sql = <<<SQL
            SELECT COUNT(1)
            FROM {table_name}
            WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
                AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid');
        SQL;

        $count = 0;
        foreach (self::TABLE_NAMES as $tableName) {
            $result = $this->connection->fetchOne(\strtr($sql, ['{table_name}' => $tableName]));
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

    private function updateProductAssociations($productAssociations): void
    {
        $values = array_map(fn (array $productAssociation): string => \strtr(
            '({product_id}, \'{quantified_associations}\', 1, CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())',
            [
                '{product_id}' => $productAssociation['id'],
                '{quantified_associations}' => \json_encode($productAssociation['quantified_associations']),
            ]
        ), $productAssociations);

        $insertSql = <<<SQL
            INSERT INTO pim_catalog_product (id, quantified_associations, is_enabled, identifier, raw_values, created, updated)
            VALUES {values}
            ON DUPLICATE KEY UPDATE quantified_associations=VALUES(quantified_associations)
        SQL;

        $this->connection->executeQuery(\strtr($insertSql, [
            '{values}' => implode(', ', $values),
        ]));
    }

    private function updateProductModelAssociations($productAssociations): void
    {
        $values = array_map(fn (array $productAssociation): string => \strtr(
            '({product_model_id}, \'{quantified_associations}\', CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())',
            [
                '{product_model_id}' => $productAssociation['id'],
                '{quantified_associations}' => \json_encode($productAssociation['quantified_associations']),
            ]
        ), $productAssociations);

        $insertSql = <<<SQL
            INSERT INTO pim_catalog_product_model (id, quantified_associations, code, raw_values, created, updated)
            VALUES {values}
            ON DUPLICATE KEY UPDATE quantified_associations=VALUES(quantified_associations)
        SQL;

        $this->connection->executeQuery(\strtr($insertSql, [
            '{values}' => implode(', ', $values),
        ]));
    }

    private function getFormerAssociations(string $tableName, $previousProductId = -1): array
    {
        $sql = <<<SQL
            SELECT id, quantified_associations
            FROM {table_name}
            WHERE JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].id')
                AND NOT JSON_CONTAINS_PATH(quantified_associations, 'one', '$.*.products[*].uuid')
                AND id > :previousProductId
            ORDER BY id
            LIMIT :limit
        SQL;

        $associations = $this->connection->fetchAllAssociative(\strtr($sql, ['{table_name}' => $tableName]), [
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

        $sql = <<<SQL
            SELECT id, BIN_TO_UUID(uuid) as uuid 
            FROM pim_catalog_product 
            WHERE id IN (:productIds)
        SQL;
        $products = $this->connection->fetchAllAssociative($sql, [
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
            $productIdToUuidMap = $dryRun ? [] : $this->getProductIdToUuidMap($associations);

            for ($pi = 0; $pi < count($associations); $pi++) {
                $formerAssociation = $associations[$pi]['quantified_associations'];
                $productId = $associations[$pi]['id'];
                $notFound = false;
                foreach ($formerAssociation as $associationName => $entityAssociations) {
                    for ($i = 0; $i < count($entityAssociations['products']); $i++) {
                        $associatedProductId = $entityAssociations['products'][$i]['id'];
                        if (array_key_exists($associatedProductId, $productIdToUuidMap)) {
                            $associatedProductUuid = $productIdToUuidMap[$associatedProductId];
                            $formerAssociation[$associationName]['products'][$i]['uuid'] = $associatedProductUuid;
                        } else {
                            if (!$dryRun) {
                                $output->writeln(sprintf('    <comment>Associated product uuid %d not found for product %d</comment>', $associatedProductId, $productId));
                            }
                            $notFound = true;
                        }
                    }
                }
                if (!$notFound) {
                    $associations[$pi]['quantified_associations'] = $formerAssociation;
                }
                $previousEntityId = $productId;
            }

            $output->writeln(sprintf('    Will update %s entities in %s table', count($associations), $tableName));
            if (!$dryRun) {
                if ($tableName === 'pim_catalog_product') {
                    $this->updateProductAssociations($associations);
                } else {
                    $this->updateProductModelAssociations($associations);
                }
                $associations = $this->getFormerAssociations($tableName, $previousEntityId);
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $associations = [];
            }
        }
    }
}

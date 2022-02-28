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
        $sqlQuantified = <<<SQL
            SELECT EXISTS(
               SELECT 1
               FROM pim_catalog_association_type
               WHERE is_quantified = 1
            ) as quantified_association
        SQL;

        $hasQuantifiedAssociationsType = $this->connection->executeQuery($sqlQuantified)->fetchOne();
        if (!(bool) $hasQuantifiedAssociationsType) {
            return false;
        }

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

    public function addMissing(bool $dryRun, OutputInterface $output): bool
    {
        $allItemsMigrated = true;
        foreach (self::TABLE_NAMES as $tableName) {
            $allItemsMigrated = $this->addMissingForTable($dryRun, $output, $tableName) && $allItemsMigrated;
        }

        return $allItemsMigrated;
    }

    private function updateAssociations(string $tableName, array $productAssociations): void
    {
        $rows = \array_map(
            fn (array $productAssociation): string => \sprintf(
                "ROW(%d, '%s')",
                $productAssociation['id'],
                \json_encode($productAssociation['quantified_associations'])
            ),
            $productAssociations
        );

        $sql = <<<SQL
        WITH
        new_quantified_associations AS (
            SELECT * FROM (VALUES {rows}) as t(id, quantified_associations)
        )
        UPDATE {tableName} p, new_quantified_associations nqa
        SET p.quantified_associations = nqa.quantified_associations
        WHERE p.id = nqa.id
        SQL;

        $this->connection->executeQuery(\strtr($sql, [
            '{rows}' => implode(',', $rows),
            '{tableName}' => $tableName,
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

    private function addMissingForTable(bool $dryRun, OutputInterface $output, string $tableName): bool
    {
        $allItemsMigrated = true;
        $previousEntityId = -1;
        $associations = $this->getFormerAssociations($tableName, $previousEntityId);
        while (count($associations) > 0) {
            $productIdToUuidMap = $dryRun ? [] : $this->getProductIdToUuidMap($associations);

            for ($pi = 0; $pi < count($associations); $pi++) {
                $formerAssociation = $associations[$pi]['quantified_associations'];
                $productId = $associations[$pi]['id'];
                $notFound = false;
                foreach ($formerAssociation as $associationName => $entityAssociations) {
                    if (array_key_exists('products', $entityAssociations)) {
                        for ($i = 0; $i < count($entityAssociations['products']); $i++) {
                            $associatedProductId = $entityAssociations['products'][$i]['id'];
                            if (array_key_exists($associatedProductId, $productIdToUuidMap)) {
                                $associatedProductUuid = $productIdToUuidMap[$associatedProductId];
                                if ($associatedProductUuid === null) {
                                    $output->writeln(sprintf('    <comment>Associated product uuid %d not found for product %d</comment>', $associatedProductId, $productId));
                                    $notFound = true;
                                    $allItemsMigrated = false;
                                } else {
                                    $formerAssociation[$associationName]['products'][$i]['uuid'] = $associatedProductUuid;
                                }
                            }
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
                $this->updateAssociations($tableName, $associations);
                $associations = $this->getFormerAssociations($tableName, $previousEntityId);
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $associations = [];
            }
        }

        return $allItemsMigrated;
    }
}

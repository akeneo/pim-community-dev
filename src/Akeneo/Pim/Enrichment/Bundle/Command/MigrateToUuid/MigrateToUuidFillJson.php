<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

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
    use StatusAwareTrait;

    private const BATCH_SIZE = 1000;
    private const TABLE_NAMES = [
        'pim_catalog_product',
        'pim_catalog_product_model'
    ];

    private LogContext $logContext;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getDescription(): string
    {
        return 'Adds product_uuid field in JSON objects';
    }

    public function getName(): string
    {
        return 'fill_json';
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

    public function addMissing(Context $context): bool
    {
        $this->logContext = $context->logContext;
        $allItemsMigrated = true;
        foreach (self::TABLE_NAMES as $tableName) {
            $this->logContext->addContext('substep', $tableName);
            $allItemsMigrated = $this->addMissingForTable($context->dryRun(), $tableName) && $allItemsMigrated;
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

    private function getProductIdToUuidMap(array $productFormerAssociations, bool $dryRun)
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
        if ($dryRun) {
            $sql = <<<SQL
            SELECT id, NULL as uuid 
            FROM pim_catalog_product 
            WHERE id IN (:productIds)
        SQL;
        }
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

    private function addMissingForTable(bool $dryRun, string $tableName): bool
    {
        $allItemsMigrated = true;
        $previousEntityId = -1;
        $formerAssociations = $this->getFormerAssociations($tableName, $previousEntityId);
        $this->logContext->addContext('table_missing_associations_counter', count($formerAssociations));

        while (count($formerAssociations) > 0) {
            $productIdToUuidMap = $this->getProductIdToUuidMap($formerAssociations, $dryRun);
            $newAssociations = $this->getNewAssociationsAndIds($formerAssociations, $productIdToUuidMap);

            $allItemsMigrated = $allItemsMigrated && \count($newAssociations) === \count($formerAssociations);
            $this->logger->notice(
                'Will update associations',
                $this->logContext->toArray(['table_association_to_update_counter' => \count($newAssociations)])
            );

            if (!$dryRun) {
                $this->updateAssociations($tableName, $newAssociations);
                $previousEntityId = \array_keys($formerAssociations)[\count($formerAssociations) - 1];
                $formerAssociations = $this->getFormerAssociations($tableName, $previousEntityId);
            } else {
                $this->logger->notice('Option --dry-run is set, will continue to next step.', $this->logContext->toArray());
                $formerAssociations = [];
            }
        }

        return $allItemsMigrated;
    }

    /**
     * Example of $formerAssociationsAndIds: [
     *     [
     *         'id' => 1234,
     *         'quantified_associations' => [
     *            "X_SELL": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     *            "PACK": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     *         ],
     *     ],
     *     [
     *         'id' => 3456,
     *         'quantified_associations' => [
     *            "X_SELL": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     *            "PACK": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     *         ],
     *     ]
     * ]
     */
    private function getNewAssociationsAndIds(array $formerAssociationsAndIds, array $productIdToUuidMap) : array
    {
        $newAssociationsAndIds = [];

        foreach ($formerAssociationsAndIds as $formerAssociationAndId) {
            $formerAssociations = $formerAssociationAndId['quantified_associations'];
            $productId = $formerAssociationAndId['id'];
            try {
                $newAssociationsAndIds[] = [
                    'quantified_associations' => $this->getNewAssociations($formerAssociations, $productIdToUuidMap),
                    'id' => $productId
                ];
            } catch (UuidNotFoundException) {
                $this->logger->warning('Missing product uuid', $this->logContext->toArray(['product_id' => $productId]));
            }
        }

        return $newAssociationsAndIds;
    }

    /**
     * Example of $formerAssociations: [
     *     "X_SELL": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     *     "PACK": ["products": [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}], "product_models": []]
     * ]
     */
    private function getNewAssociations(array $formerAssociations, array $productIdToUuidMap): array
    {
        $newAssociations = [];
        foreach ($formerAssociations as $associationType => $associationsByType) {
            $newAssociations[$associationType] = [];
            if (\array_key_exists('product_models', $associationsByType)) {
                $newAssociations[$associationType]['product_models'] = $associationsByType['product_models'];
            }
            if (\array_key_exists('products', $associationsByType)) {
                $newAssociations[$associationType]['products'] = $this->getNewProductAssociations($associationsByType['products'], $productIdToUuidMap);
            }
        }

        return $newAssociations;
    }

    /**
     * Example of $formerProductAssociations: [{"id": 100, "quantity": 1}, {"id": 1000, "quantity": 1}]
     */
    private function getNewProductAssociations(array $formerProductAssociations, array $productIdToUuidMap): array
    {
        $newProductAssociations = [];
        foreach ($formerProductAssociations as $formerProductAssociation) {
            $associatedProductId = $formerProductAssociation['id'];
            if (array_key_exists($associatedProductId, $productIdToUuidMap)) {
                // the product exists
                $associatedProductUuid = $productIdToUuidMap[$associatedProductId];
                if ($associatedProductUuid === null) {
                    // uuid does not exist yet
                    throw new UuidNotFoundException();
                }
                $newProductAssociations[] = [
                    'id' => $associatedProductId,
                    'uuid' => $associatedProductUuid,
                    'quantity' => $formerProductAssociation['quantity']
                ];
            }
        }

        return $newProductAssociations;
    }
}

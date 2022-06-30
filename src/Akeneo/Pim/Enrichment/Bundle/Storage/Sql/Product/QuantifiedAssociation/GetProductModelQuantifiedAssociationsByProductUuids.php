<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

final class GetProductModelQuantifiedAssociationsByProductUuids
{
    public function __construct(
        private Connection $connection,
        private GetIdMappingFromProductModelIdsQuery $getIdMappingFromProductModelIdsQuery,
        private FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
    }

    /**
     * Executes SQL query to get product model quantified associations from a set of product identifiers.
     * Returns an array like:
     * [
     *      'uuidProductA' => [
     *          'PACK' => [
     *              'product_models' => [
     *                  ['identified' => 'productModelA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductUuids(array $productUuids): array
    {
        if (empty($productUuids)) {
            return [];
        }

        $rows = $this->fetchQuantifiedAssociations($productUuids);

        return $this->hydrateQuantifiedAssociations($rows);
    }

    private function fetchQuantifiedAssociations(array $productUuids): array
    {
        $query = <<<SQL
SELECT
    BIN_TO_UUID(p.uuid) AS uuid,
    JSON_MERGE_PRESERVE(COALESCE(pm2.quantified_associations, '{}'), COALESCE(pm1.quantified_associations, '{}'), COALESCE(p.quantified_associations, '{}')) AS all_quantified_associations
FROM pim_catalog_product p
LEFT JOIN pim_catalog_product_model pm1 ON p.product_model_id = pm1.id
LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
WHERE p.uuid IN (:productUuids)
;
SQL;

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $rows = $this->connection->executeQuery(
            $query,
            ['productUuids' => $uuidsAsBytes],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return $rows;
    }

    private function hydrateQuantifiedAssociations($rows): array
    {
        $validQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationTypeCodes->execute();

        $results = [];
        foreach ($rows as $row) {
            if (null === $row['all_quantified_associations']) {
                continue;
            }
            $allQuantifiedAssociationsWithProductId = [];
            $allQuantifiedAssociationsWithProductIdFromJson = json_decode($row['all_quantified_associations'], true);
            foreach ($allQuantifiedAssociationsWithProductIdFromJson as $key => $value) {
                $allQuantifiedAssociationsWithProductId[\strval($key)] = $value;
            }
            $associationWithIdentifiers = $this->associationsWithIdentifiers(
                $allQuantifiedAssociationsWithProductId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithIdentifiers)) {
                $productIdentifier = $row['uuid'];
                $results[$productIdentifier] = $associationWithIdentifiers;
            }
        }

        return $results;
    }

    private function associationsWithIdentifiers(
        array $allQuantifiedAssociationsWithProductId,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productIds = [];
        foreach ($allQuantifiedAssociationsWithProductId as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productIds = array_merge($productIds, $this->productIds($quantifiedAssociationWithId));
        }

        $productIdMapping = $this->getIdMappingFromProductModelIdsQuery->execute($productIds);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductId as $associationTypeCode => $associationWithIds) {
            if (empty($associationWithIds) || !is_string($associationTypeCode)) {
                continue;
            }

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithIds['product_models'] as $associationWithProductId) {
                try {
                    $identifier = $productIdMapping->getIdentifier($associationWithProductId['id']);
                } catch (\Exception $exception) {
                    continue;
                }
                $uniqueQuantifiedAssociations[$identifier] = [
                    'identifier' => $identifier,
                    'quantity'   => (int) $associationWithProductId['quantity']
                ];
            }
            if (!empty($uniqueQuantifiedAssociations)) {
                $result[$associationTypeCode]['product_models'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }

    private function productIds(array $quantifiedAssociationWithProductId): array
    {
        return array_map(
            function (array $quantifiedAssociations) {
                return $quantifiedAssociations['id'];
            },
            $quantifiedAssociationWithProductId['product_models'] ?? []
        );
    }
}

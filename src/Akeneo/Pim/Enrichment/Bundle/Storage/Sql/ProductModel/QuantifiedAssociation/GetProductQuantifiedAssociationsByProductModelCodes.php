<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetUuidMappingQuery;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetProductQuantifiedAssociationsByProductModelCodes
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetUuidMappingQuery $getUuidMappingQuery,
        private readonly FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product model codes.
     * Returns an array like:
     * [
     *      'productModelA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['identified' => 'productA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $rows = $this->fetchQuantifiedAssociations($productModelCodes);

        return $this->hydrateQuantifiedAssociations($rows);
    }

    private function fetchQuantifiedAssociations(array $productModelCodes): array
    {
        $query = <<<SQL
SELECT
    product_model.code,
    JSON_MERGE_PRESERVE(COALESCE(parent_product_model.quantified_associations, '{}'), COALESCE(product_model.quantified_associations, '{}')) AS all_quantified_associations
FROM pim_catalog_product_model as product_model
LEFT JOIN pim_catalog_product_model parent_product_model ON parent_product_model.id = product_model.parent_id
WHERE product_model.code IN (:productModelCodes)
;
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
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
            $allQuantifiedAssociationsWithProductId = json_decode($row['all_quantified_associations'], true);
            $associationWithUuids = $this->associationsWithUuids(
                $allQuantifiedAssociationsWithProductId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithUuids)) {
                $productModelCode = $row['code'];
                $results[$productModelCode] = $associationWithUuids;
            }
        }

        return $results;
    }

    private function associationsWithUuids(
        array $allQuantifiedAssociationsWithProductIds,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productUuids = [];
        foreach ($allQuantifiedAssociationsWithProductIds as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productUuids = array_merge($productUuids, $this->productUuids($quantifiedAssociationWithId));
        }
        $productUuids = array_map(static fn (string $uuidAsString): UuidInterface => Uuid::fromString($uuidAsString), $productUuids);

        $productUuidMapping = $this->getUuidMappingQuery->fromProductIds([], $productUuids);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductIds as $associationTypeCode => $associationWithIds) {
            if (empty($associationWithIds) || !is_string($associationTypeCode)) {
                continue;
            }

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithIds['products'] as $associationWithProductId) {
                if (!$productUuidMapping->hasUuidFromId($associationWithProductId['id'])) {
                    // the product does not exist anymore
                    continue;
                }

                $uniqueQuantifiedAssociations[$associationWithProductId['uuid']] = [
                    'identifier' => $productUuidMapping->getIdentifierFromId($associationWithProductId['id']),
                    'quantity' => (int) $associationWithProductId['quantity'],
                    'uuid' => $associationWithProductId['uuid'],
                ];
            }
            if (!empty($uniqueQuantifiedAssociations)) {
                $result[$associationTypeCode]['products'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }

    private function productUuids(array $quantifiedAssociationWithProductUuids): array
    {
        return array_map(
            function (array $quantifiedAssociations) {
                return $quantifiedAssociations['uuid'];
            },
            $quantifiedAssociationWithProductUuids['products'] ?? []
        );
    }
}

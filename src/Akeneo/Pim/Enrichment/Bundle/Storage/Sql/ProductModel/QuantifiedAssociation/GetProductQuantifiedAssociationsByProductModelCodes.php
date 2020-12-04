<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;

final class GetProductQuantifiedAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    /** @var GetIdMappingFromProductIdsQuery */
    private $getIdMappingFromProductIdsQuery;

    /** @var FindQuantifiedAssociationTypeCodesInterface */
    private $findQuantifiedAssociationTypeCodes;

    public function __construct(
        Connection $connection,
        GetIdMappingFromProductIdsQuery $getIdMappingFromProductIdsQuery,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
        $this->connection = $connection;
        $this->getIdMappingFromProductIdsQuery = $getIdMappingFromProductIdsQuery;
        $this->findQuantifiedAssociationTypeCodes = $findQuantifiedAssociationTypeCodes;
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
        )->fetchAll();

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
            $associationWithIdentifiers = $this->associationsWithIdentifiers(
                $allQuantifiedAssociationsWithProductId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithIdentifiers)) {
                $productIdentifier = $row['code'];
                $results[$productIdentifier] = $associationWithIdentifiers;
            }
        }

        return $results;
    }

    private function associationsWithIdentifiers(
        array $allQuantifiedAssociationsWithProductIds,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productIdMapping = $this->fetchIdMapping($allQuantifiedAssociationsWithProductIds);

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
                $result[$associationTypeCode]['products'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }

    private function productModelCodes(array $quantifiedAssociationWithProductModelId): array
    {
        return array_map(
            function (array $quantifiedAssociations) {
                return $quantifiedAssociations['id'];
            },
            $quantifiedAssociationWithProductModelId['products'] ?? []
        );
    }

    private function fetchIdMapping(array $allQuantifiedAssociationsWithProductModelIds): IdMapping
    {
        $productModelCodes = [];
        foreach ($allQuantifiedAssociationsWithProductModelIds as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productModelCodes = array_merge(
                $productModelCodes,
                $this->productModelCodes($quantifiedAssociationWithId)
            );
        }

        return $this->getIdMappingFromProductIdsQuery->execute($productModelCodes);
    }
}

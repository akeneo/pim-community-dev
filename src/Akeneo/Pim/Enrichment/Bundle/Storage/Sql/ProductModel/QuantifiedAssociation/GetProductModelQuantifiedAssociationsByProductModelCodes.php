<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;

final class GetProductModelQuantifiedAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    /** @var GetIdMappingFromProductModelIdsQuery */
    private $getIdMappingFromProductModelIdsQuery;

    /** @var FindQuantifiedAssociationTypeCodesInterface */
    private $findQuantifiedAssociationTypeCodes;

    public function __construct(
        Connection $connection,
        GetIdMappingFromProductModelIdsQuery $getIdMappingFromProductModelIdsQuery,
        FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
        $this->connection = $connection;
        $this->getIdMappingFromProductModelIdsQuery = $getIdMappingFromProductModelIdsQuery;
        $this->findQuantifiedAssociationTypeCodes = $findQuantifiedAssociationTypeCodes;
    }

    /**
     * Executes SQL query to get product model quantified associations from a set of product model codes.
     * Returns an array like:
     * [
     *      'productModelA' => [
     *          'PACK' => [
     *              'product_models' => [
     *                  ['identified' => 'productModelB','quantity' => 5]
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
            $allQuantifiedAssociationsWithProductModelId = json_decode($row['all_quantified_associations'], true);
            $associationWithCodes = $this->associationsWithCodes(
                $allQuantifiedAssociationsWithProductModelId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithCodes)) {
                $productModelCode = $row['code'];
                $results[$productModelCode] = $associationWithCodes;
            }
        }

        return $results;
    }

    private function associationsWithCodes(
        array $allQuantifiedAssociationsWithProductModelIds,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productModelIdMapping = $this->fetchIdMapping($allQuantifiedAssociationsWithProductModelIds);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductModelIds as $associationTypeCode => $associationWithIds) {
            if (empty($associationWithIds) || !is_string($associationTypeCode)) {
                continue;
            }

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithIds['product_models'] as $associationWithProductModelId) {
                try {
                    $code = $productModelIdMapping->getIdentifier($associationWithProductModelId['id']);
                } catch (\Exception $exception) {
                    continue;
                }
                $uniqueQuantifiedAssociations[$code] = [
                    'identifier' => $code,
                    'quantity'   => (int) $associationWithProductModelId['quantity']
                ];
            }
            if (!empty($uniqueQuantifiedAssociations)) {
                $result[$associationTypeCode]['product_models'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }

    private function productModelIds(array $quantifiedAssociationWithProductModelId): array
    {
        return array_map(
            function (array $quantifiedAssociations) {
                return $quantifiedAssociations['id'];
            },
            $quantifiedAssociationWithProductModelId['product_models'] ?? []
        );
    }

    private function fetchIdMapping(array $allQuantifiedAssociationsWithProductModelIds
    ): IdMapping {
        $productModelIds = [];
        foreach ($allQuantifiedAssociationsWithProductModelIds as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productModelIds = array_merge($productModelIds, $this->productModelIds($quantifiedAssociationWithId));
        }

        return $this->getIdMappingFromProductModelIdsQuery->execute($productModelIds);
    }
}

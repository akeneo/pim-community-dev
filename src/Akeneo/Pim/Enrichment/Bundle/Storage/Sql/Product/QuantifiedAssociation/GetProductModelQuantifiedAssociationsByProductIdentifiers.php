<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQuery;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;

final class GetProductModelQuantifiedAssociationsByProductIdentifiers
{
    /** @var Connection */
    private $connection;

    /** @var GetIdMappingFromProductIdsQuery */
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
     * Executes SQL query to get product model quantified associations from a set of product identifiers.
     * Returns an array like:
     * [
     *      'productA' => [
     *          'PACK' => [
     *              'product_models' => [
     *                  ['identified' => 'productModelA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $rows = $this->fetchQuantifiedAssociations($productIdentifiers);

        return $this->hydrateQuantifiedAssociations($rows);
    }

    private function fetchQuantifiedAssociations(array $productIdentifiers): array
    {
        $query = <<<SQL
SELECT
    p.identifier,
    JSON_MERGE_PRESERVE(COALESCE(pm2.quantified_associations, '{}'), COALESCE(pm1.quantified_associations, '{}'), COALESCE(p.quantified_associations, '{}')) AS all_quantified_associations
FROM pim_catalog_product p
LEFT JOIN pim_catalog_product_model pm1 ON p.product_model_id = pm1.id
LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
WHERE p.identifier IN (:productIdentifiers)
;
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
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
                $productIdentifier = $row['identifier'];
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

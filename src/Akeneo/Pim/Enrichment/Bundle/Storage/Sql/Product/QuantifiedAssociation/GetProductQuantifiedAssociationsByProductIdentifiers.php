<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQuery;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Doctrine\DBAL\Connection;

final class GetProductQuantifiedAssociationsByProductIdentifiers
{
    /** @var Connection */
    private $connection;

    /** @var GetIdMappingFromProductIdsQuery */
    private $getIdMappingFromProductIdsQuery;

    /** @var AssociationTypeRepository */
    private $associationTypeRepository;

    public function __construct(
        Connection $connection,
        GetIdMappingFromProductIdsQuery $getIdMappingFromProductIdsQuery,
        AssociationTypeRepository $associationTypeRepository
    ) {
        $this->connection = $connection;
        $this->getIdMappingFromProductIdsQuery = $getIdMappingFromProductIdsQuery;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product identifiers.
     * Returns an array like:
     * [
     *      'productA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['identifier' => 'productB','quantity' => 5]
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
GROUP BY p.id, p.identifier
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
        $results = [];
        foreach ($rows as $row) {
            if (null === $row['all_quantified_associations']) {
                continue;
            }
            $allQuantifiedAssociationsWithProductId = json_decode($row['all_quantified_associations'], true);
            $associationWithIdentifiers = $this->associationsWithIdentifiers($allQuantifiedAssociationsWithProductId);
            if (!empty($associationWithIdentifiers)) {
                $productIdentifier = $row['identifier'];
                $results[$productIdentifier] = $associationWithIdentifiers;
            }
        }

        return $results;
    }

    private function associationsWithIdentifiers(array $allQuantifiedAssociationsWithProductId)
    {
        $productIds = [];
        foreach ($allQuantifiedAssociationsWithProductId as $quantifiedAssociationWithId) {
            $productIds = array_merge($productIds, $this->productIds($quantifiedAssociationWithId));
        }

        $productIdMapping = $this->getIdMappingFromProductIdsQuery->execute($productIds);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductId as $associationTypeCode => $associationWithIds) {
            if (!$this->associationTypeExists($associationTypeCode)) {
                continue;
            }
            foreach ($associationWithIds['products'] as $associationWithProductId) {
                try {
                    $identifier = $productIdMapping->getIdentifier($associationWithProductId['id']);
                } catch (\Exception $exception) {
                    continue;
                }
                $result[$associationTypeCode]['products'][] = [
                    'identifier' => $identifier,
                    'quantity'   => (int) $associationWithProductId['quantity']
                ];
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
            $quantifiedAssociationWithProductId['products']
        );
    }

    private function associationTypeExists(string $associationTypeCode): bool
    {
        return null !== $this->associationTypeRepository->findOneByIdentifier($associationTypeCode);
    }
}

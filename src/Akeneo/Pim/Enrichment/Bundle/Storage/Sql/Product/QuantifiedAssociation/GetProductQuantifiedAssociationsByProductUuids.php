<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetProductQuantifiedAssociationsByProductUuids
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetUuidMappingQueryInterface $getUuidMappingQuery,
        private readonly FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes,
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product identifiers.
     * Returns an array like:
     * [
     *      'productA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['identifier' => 'productB','quantity' => 5, 'uuid' => 'uuidProductB']
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
            $allQuantifiedAssociationsWithProductId = json_decode($row['all_quantified_associations'], true);
            $associationWithIdentifiers = $this->associationsWithUuids(
                $allQuantifiedAssociationsWithProductId,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithIdentifiers)) {
                $productUuid = $row['uuid'];
                $results[$productUuid] = $associationWithIdentifiers;
            }
        }

        return $results;
    }

    private function associationsWithUuids(
        array $allQuantifiedAssociationsWithProductId,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $productUuids = [];
        foreach ($allQuantifiedAssociationsWithProductId as $quantifiedAssociationWithId) {
            if (empty($quantifiedAssociationWithId)) {
                continue;
            }
            $productUuids = array_merge($productUuids, $this->productUuids($quantifiedAssociationWithId));
        }

        $productUuids = array_map(static fn(string $uuidAsString): UuidInterface => Uuid::fromString($uuidAsString), $productUuids);

        $productUuidMapping = $this->getUuidMappingQuery->fromProductIds([], $productUuids);

        $result = [];
        foreach ($allQuantifiedAssociationsWithProductId as $associationTypeCode => $associationWithUuids) {
            if (empty($associationWithUuids)) {
                continue;
            }

            $associationTypeCode = (string) $associationTypeCode;

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithUuids['products'] as $associationWithProductUuid) {
                if (!$this->productExists($associationWithProductUuid['uuid'])) {
                    continue;
                }

                try {
                    $identifier = $productUuidMapping->getIdentifierFromId($associationWithProductUuid['id']);
                } catch (\Exception $exception) {
                    continue;
                }
                $uniqueQuantifiedAssociations[$identifier] = [
                    'identifier' => $identifier,
                    'quantity' => (int)$associationWithProductUuid['quantity'],
                    'uuid' => $associationWithProductUuid['uuid'],
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

    private function productExists(string $uuid): bool
    {
        return null !== $this->productRepository->find($uuid);
    }
}

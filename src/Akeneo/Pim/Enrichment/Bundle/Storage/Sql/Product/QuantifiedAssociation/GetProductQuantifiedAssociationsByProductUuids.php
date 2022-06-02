<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetProductQuantifiedAssociationsByProductUuids
{
    public function __construct(
        private Connection $connection,
        private FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product uuids.
     * @param Uuid[] $productUuids
     * @return array
     * Returns an array like:
     * [
     *      'productA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['uuid' => 'ba503882-a2dd-4a8b-a1f3-13fd96a35c7d','quantity' => 5]
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

    /**
     * @param Uuid[] $productUuids
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchQuantifiedAssociations(array $productUuids): array
    {
        $query = <<<SQL
SELECT
    BIN_TO_UUID(p.uuid) as uuid,
    JSON_MERGE_PRESERVE(COALESCE(pm2.quantified_associations, '{}'), COALESCE(pm1.quantified_associations, '{}'), COALESCE(p.quantified_associations, '{}')) AS all_quantified_associations
FROM pim_catalog_product p
LEFT JOIN pim_catalog_product_model pm1 ON p.product_model_id = pm1.id
LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
WHERE p.uuid IN (:productUuids)
;
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productUuids' => \array_map(fn (UuidInterface $uuid): string => Uuid::fromString($uuid)->getBytes(), $productUuids)],
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
            $allQuantifiedAssociationsWithProductUuid = json_decode($row['all_quantified_associations'], true);
            $associationWithUuids = $this->associationsWithUuids(
                $allQuantifiedAssociationsWithProductUuid,
                $validQuantifiedAssociationTypeCodes
            );
            if (!empty($associationWithUuids)) {
                $results[$row['uuid']] = $associationWithUuids;
            }
        }

        return $results;
    }

    private function associationsWithUuids(
        array $allQuantifiedAssociationsWithProductUuid,
        array $validQuantifiedAssociationTypeCodes
    ) {
        $result = [];
        foreach ($allQuantifiedAssociationsWithProductUuid as $associationTypeCode => $associationWithUuids) {
            if (empty($associationWithUuids) || !is_string($associationTypeCode)) {
                continue;
            }

            if (!in_array($associationTypeCode, $validQuantifiedAssociationTypeCodes)) {
                continue;
            }

            $uniqueQuantifiedAssociations = [];
            foreach ($associationWithUuids['products'] as $associationWithProductUuid) {
                $productUuid = $associationWithProductUuid['uuid'];

                $uniqueQuantifiedAssociations[$productUuid] = [
                    'uuid' => $productUuid,
                    'quantity' => (int)$associationWithProductUuid['quantity']
                ];
            }
            if (!empty($uniqueQuantifiedAssociations)) {
                $result[$associationTypeCode]['products'] = array_values($uniqueQuantifiedAssociations);
            }
        }

        return $result;
    }
}

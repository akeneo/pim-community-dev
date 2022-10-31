<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductUuidsByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

final class GetAssociatedProductUuidsByProductFromDB implements GetAssociatedProductUuidsByProduct
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifiers(UuidInterface $productUuid, AssociationInterface $association): array
    {
        $sql = <<<SQL
SELECT DISTINCT(p.identifier) as identifier, p.uuid AS p_uuid
FROM pim_catalog_association a
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.uuid = ap.product_uuid
WHERE a.owner_uuid = UUID_TO_BIN(:ownerUuid) AND a.association_type_id = :associationTypeId
ORDER BY p_uuid;
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'ownerUuid' => $productUuid->toString(),
                'associationTypeId' => $association->getAssociationType()->getId()
            ]
        );

        return $stmt->fetchFirstColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getUuids(UuidInterface $productUuid, AssociationInterface $association): array
    {
        $sql = <<<SQL
SELECT DISTINCT(BIN_TO_UUID(p.uuid)) as uuid, p.uuid AS p_uuid
FROM pim_catalog_association a
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.uuid = ap.product_uuid
WHERE a.owner_uuid = UUID_TO_BIN(:ownerUuid) AND a.association_type_id = :associationTypeId
ORDER BY p_uuid;
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'ownerUuid' => $productUuid->toString(),
                'associationTypeId' => $association->getAssociationType()->getId()
            ]
        );

        return $stmt->fetchFirstColumn();
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductCodesByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class GetAssociatedProductCodesByProductFromDB implements GetAssociatedProductCodesByProduct
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodes(int $productId, AssociationInterface $association)
    {
        $associationTable = $association instanceof ProductModelAssociationInterface ? 'pim_catalog_product_model_association' : 'pim_catalog_association';
        $associationProductTable = $association instanceof ProductModelAssociationInterface ? 'pim_catalog_association_product_model_to_product' : 'pim_catalog_association_product';

        $sql = <<<SQL
SELECT DISTINCT(p.identifier) as code
FROM $associationTable a
    INNER JOIN $associationProductTable ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.id = ap.product_id
WHERE a.owner_id = :ownerId AND a.association_type_id = :associationTypeId
ORDER BY p.identifier ASC;
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'ownerId'           => $productId,
                'associationTypeId' => $association->getAssociationType()->getId()
            ],
            [
                'ownerId'           => \PDO:: PARAM_INT,
                'associationTypeId' => \PDO:: PARAM_INT
            ]
        );

        return $stmt->fetchAll(FetchMode::COLUMN);
    }
}

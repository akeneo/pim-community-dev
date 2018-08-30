<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;

class GetAssociatedProductCodesByProductFromDB implements GetAssociatedProductCodesByProduct
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $associationClass;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
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
        $stmt = $this->connection->executeQuery($sql,
            [
                'ownerId'           => $productId,
                'associationTypeId' => $association->getAssociationType()->getId()
            ],
            [
                'ownerId'           => \PDO:: PARAM_INT,
                'associationTypeId' => \PDO:: PARAM_INT
            ]
        );

        $codes = array_map(function ($row) {
            return $row['code'];
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        return $codes;
    }
}

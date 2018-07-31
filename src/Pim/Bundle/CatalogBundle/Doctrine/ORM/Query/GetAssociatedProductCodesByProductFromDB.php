<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;

class GetAssociatedProductCodesByProductFromDB implements GetAssociatedProductCodesByProduct
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $associationClass;

    public function __construct(EntityManagerInterface $entityManager, $associationClass)
    {
        $this->entityManager = $entityManager;
        $this->associationClass = $associationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodes($productId, $associationTypeId)
    {
        $associations = $this->entityManager->createQueryBuilder()
            ->select('v.varchar')
            ->from($this->associationClass, 'a')
            ->innerJoin('a.products', 'p')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'attr')
            ->where('a.owner = :ownerId')
            ->andWhere('attr.type = :identifierProductValue')
            ->andWhere('a.associationType = :associationTypeId')
            ->setParameters([
                'ownerId' => $productId,
                'identifierProductValue' => AttributeTypes::IDENTIFIER,
                'associationTypeId' => $associationTypeId,
            ])
            ->orderBy('v.varchar')
            ->getQuery()
            ->getResult();

        return array_map(function (array $association) {
            return $association['varchar'];
        }, $associations);
    }
}

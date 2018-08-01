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
            ->select('p.identifier')
            ->from($this->associationClass, 'a')
            ->innerJoin('a.products', 'p')
            ->where('a.owner = :ownerId')
            ->andWhere('a.associationType = :associationTypeId')
            ->setParameters([
                'ownerId' => $productId,
                'associationTypeId' => $associationTypeId,
            ])
            ->orderBy('p.identifier')
            ->getQuery()
            ->getResult();

        return array_map(function (array $association) {
            return $association['identifier'];
        }, $associations);
    }
}

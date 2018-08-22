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

    public function __construct(EntityManagerInterface $entityManager, $associationClass)
    {
        $this->entityManager = $entityManager;
        $this->associationClass = $associationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodes(AssociationInterface $association)
    {
        $associations = $this->entityManager->createQueryBuilder()
            ->select('p.identifier')
            ->from(get_class($association), 'a')
            ->innerJoin('a.products', 'p')
            ->andWhere('a.id = :associationId')
            ->setParameters([
                'associationId' => $association->getId(),
            ])
            ->orderBy('p.identifier')
            ->getQuery()
            ->getResult();

        return array_map(function (array $association) {
            return $association['identifier'];
        }, $associations);
    }
}

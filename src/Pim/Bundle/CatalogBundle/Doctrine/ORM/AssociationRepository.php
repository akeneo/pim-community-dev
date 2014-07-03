<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Association repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends EntityRepository implements
    AssociationRepositoryInterface,
    ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countForAssociationType(AssociationType $associationType)
    {
        $qb = $this->createQueryBuilder('pa');

        $qb
            ->select(
                $qb->expr()->countDistinct('pa.id')
            )
            ->leftJoin('pa.products', 'products')
            ->leftJoin('pa.groups', 'groups')
            ->where('pa.associationType = :association_type')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('products'),
                    $qb->expr()->isNotNull('groups')
                )
            )
            ->setParameter('association_type', $associationType);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        list($productCode, $associationCode) = explode('.', $code);

        return $this->createQueryBuilder('pass')
            ->select('pass')
            ->innerJoin('pass.owner', 'p')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'at')
            ->innerJoin('pass.associationType', 'assType')
            ->where('at.attributeType=:identifier_type')
            ->andWhere('v.varchar=:product_code')
            ->andWhere('assType.code=:association_code')
            ->setParameter('identifier_type', 'pim_catalog_identifier')
            ->setParameter('product_code', $productCode)
            ->setParameter('association_code', $associationCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('owner', 'associationType');
    }

    /**
     * {@inheritdoc}
     */
    public function findByProductIdAndOwnerIds($productId, array $ownerIds)
    {
        $qb = $this->createQueryBuilder('pa');

        $qb
            ->join('pa.products', 'pap', Join::WITH, 'pap.id = :productId')
            ->where($qb->expr()->in('pa.owner', $ownerIds))
            ->setParameter(':productId', $productId);

        return $qb->getQuery()->getResult();
    }
}

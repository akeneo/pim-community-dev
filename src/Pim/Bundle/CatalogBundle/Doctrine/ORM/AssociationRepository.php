<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Association repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class AssociationRepository extends EntityRepository implements
    AssociationRepositoryInterface,
    ReferableEntityRepositoryInterface,
    IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countForAssociationType(AssociationTypeInterface $associationType)
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
    public function findOneByIdentifier($code)
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
    public function getIdentifierProperties()
    {
        return array('owner', 'associationType');
    }

    /**
     * {@inheritdoc}
     */
    public function findByProductAndOwnerIds(ProductInterface $product, array $ownerIds)
    {
        $qb = $this->createQueryBuilder('pa');

        $qb
            ->join('pa.products', 'pap', Join::WITH, 'pap.id = :productId')
            ->where($qb->expr()->in('pa.owner', $ownerIds))
            ->setParameter(':productId', $product->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function getReferenceProperties()
    {
        return $this->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function findByReference($code)
    {
        return $this->findOneByIdentifier($code);
    }
}

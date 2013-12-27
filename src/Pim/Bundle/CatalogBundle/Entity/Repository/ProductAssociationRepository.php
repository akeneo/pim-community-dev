<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Product association repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationRepository extends EntityRepository implements ReferableEntityRepositoryInterface
{
    /**
     * Return the number of ProductAssociations for a specific association
     *
     * @param Association $association
     *
     * @return mixed
     */
    public function countForAssociation(Association $association)
    {
        $qb = $this->createQueryBuilder('pa');

        $qb
            ->select(
                $qb->expr()->countDistinct('pa.id')
            )
            ->leftJoin('pa.products', 'products')
            ->leftJoin('pa.groups', 'groups')
            ->where('pa.association = :association')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('products'),
                    $qb->expr()->isNotNull('groups')
                )
            )
            ->setParameter('association', $association);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->createQueryBuilder('pass')
            ->select('pass')
            ->innerJoin('pass.owner', 'p')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where('a.attributeType=:identifier_type')
            ->andWhere('p.varchar=:code')
            ->setParameter('identifier_type', 'pim_catalog_identifier')
            ->setParameter('code', $code)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('owner', 'association');
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AssociationRepositoryInterface;

/**
 * Association repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends EntityRepository implements AssociationRepositoryInterface
{
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
}

<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product association repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends EntityRepository
{
    /**
     * Build all association entities not yet linked to a product
     *
     * @param ProductInterface $product
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildMissingAssociations(ProductInterface $product)
    {
        $qb = $this->createQueryBuilder('pa');

        $associationIds = $product->getProductAssociations()->map(
            function ($productAssociation) {
                return $productAssociation->getAssociation()->getId();
            }
        );

        if (!empty($associationIds)) {
            $qb->andWhere(
                $qb->expr()->notIn('pa.id', $associationIds->toArray())
            );
        }

        return $qb;
    }
}

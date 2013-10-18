<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Product;

/**
 * Product association repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends EntityRepository
{
    public function buildMissingAssociations(Product $product)
    {
        $qb = $this->createQueryBuilder('pa');

        $associationIds = $product->getProductAssociations()->map(function ($productAssociation) {
            return $productAssociation->getAssociation()->getId();
        });

        $qb->andWhere(
            $qb->expr()->notIn('pa.id', $associationIds->toArray())
        );

        return $qb;
    }
}

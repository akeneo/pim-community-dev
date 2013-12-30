<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Association repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends ReferableEntityRepository
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
        $qb = $this->createQueryBuilder('a');

        if ($productAssociations = $product->getProductAssociations()) {
            $associationIds = $productAssociations->map(
                function ($productAssociation) {
                    return $productAssociation->getAssociation()->getId();
                }
            );

            if (!$associationIds->isEmpty()) {
                $qb->andWhere(
                    $qb->expr()->notIn('a.id', $associationIds->toArray())
                );
            }
        }

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('a');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            "(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)",
            $rootAlias
        );

        $qb
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS label", $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        return $qb;
    }
}

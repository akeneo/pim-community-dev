<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository
{
    /**
     * Add join to values tables
     *
     * @param QueryBuilder $qb
     */
    protected function addJoinToValueTables(QueryBuilder $qb)
    {
        parent::addJoinToValueTables($qb);

        $qb->addSelect('ValueMetric')
           ->leftJoin('Value.metric', 'ValueMetric')
           ->addSelect('ValuePrices')
           ->leftJoin('Value.prices', 'ValuePrices')
           ->addSelect('ValueMedia')
           ->leftJoin('Value.media', 'ValueMedia')
           ->addSelect('AttributeTranslation')
           ->leftJoin('Attribute.translations', 'AttributeTranslation')
           ->addSelect('Family')
           ->leftJoin($this->entityAlias.'.family', 'Family')
           ->addSelect('FamilyTranslation')
           ->leftJoin('Family.translations', 'FamilyTranslation')
           ->addSelect('AttributeGroup')
           ->leftJoin('Attribute.group', 'AttributeGroup')
           ->addSelect('AttributeGroupTranslation')
           ->leftJoin('AttributeGroup.translations', 'AttributeGroupTranslation');
    }

    public function buildByScope($scope)
    {
        $qb = $this->findByWithAttributesQB();

        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('Value.scope', '?1'),
                    $qb->expr()->isNull('Value.scope')
                )
            )
            ->setParameter(1, $scope);
    }
}

<?php

namespace PimEnterprise\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository as PimAttributeRepository;

/**
 * Override attribute repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeRepository extends PimAttributeRepository
{
    /**
     * {@inheritdoc}
     */
    protected function findWithGroupsQB(array $attributeIds = array(), array $criterias = array())
    {
        $qb = parent::findWithGroupsQB($attributeIds, $criterias);

        if (isset($criterias['filters'])) {
            foreach ($criterias['filter'] as $criteria => $value) {
                $qb->andWhere($qb->expr()->eq(sprintf('a.%s', $criteria), $value));
            }
        }

        return $qb->getQuery()->execute();
    }
}

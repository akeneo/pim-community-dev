<?php

namespace PimEnterprise\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository as BaseAttributeRepository;

/**
 * Override attribute repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeRepository extends BaseAttributeRepository
{
    /**
     * {@inheritdoc}
     */
    protected function findWithGroupsQB(array $attributeIds = array(), array $criterias = array())
    {
        $qb = parent::findWithGroupsQB($attributeIds, $criterias);

        if (isset($criterias['filters'])) {
            foreach ($criterias['filters'] as $field => $subQB) {
                $qb->andWhere(
                    $qb->expr()->in($field, $subQB->getDQL())
                );
                $qb->setParameters($subQB->getParameters());
            }
        }

        return $qb;
    }
}

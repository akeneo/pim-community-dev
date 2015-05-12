<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository as BaseAttributeRepository;
use PimEnterprise\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Override attribute repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeRepository extends BaseAttributeRepository implements AttributeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function findWithGroupsQB(array $attributeIds = [], array $criterias = [])
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

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.code, a.attributeType')
            ->where('a.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getArrayResult();

        $attributes = [];
        if (!empty($results)) {
            foreach ($results as $attribute) {
                $attributes[$attribute['code']] = $attribute['attributeType'];
            }
        }

        return $attributes;
    }
}

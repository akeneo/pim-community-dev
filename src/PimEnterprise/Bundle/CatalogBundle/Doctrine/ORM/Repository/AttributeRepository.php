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

/**
 * Override attribute repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeRepository extends BaseAttributeRepository
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
    public function findAttributeCodesUsableInGrid($groupIds = null)
    {
        $qb = $this->createQueryBuilder('att')
            ->select('att.code');

        if (is_array($groupIds)) {
            if (empty($groupIds)) {
                return [];
            }

            $qb->andWhere('att.group IN (:groupIds)');
            $qb->setParameter('groupIds', $groupIds);
        }

        $qb->andWhere('att.useableAsGridFilter = :useableInGrid');
        $qb->setParameter('useableInGrid', 1);

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, 'code');
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributeCodes()
    {
        $qb = $this->createQueryBuilder('att')
            ->select('att.code');

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, 'code');
    }
}

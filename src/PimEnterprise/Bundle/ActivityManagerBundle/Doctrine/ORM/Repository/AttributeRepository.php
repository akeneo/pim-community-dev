<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Repository\AttributeRepositoryInterface;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AttributeRepository extends EntityRepository implements AttributeRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
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

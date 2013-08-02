<?php

namespace Oro\Bundle\EntityConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ConfigLogRepository extends EntityRepository
{
    public function getHistoryQuery($className = null, $fieldName = null, $scope = null)
    {
        $qb = $this->createQueryBuilder('cl')
            ->select('cld.diff as diff, cld.className as className, cld.fieldName as fieldName, cld.scope as scope')
            ->addSelect('cl.id as logId')
            ->addSelect('u.username as username')
            ->leftJoin('cl.diffs', 'cld')
            ->leftJoin('cl.user', 'u');

        if (!$className) {
            return $qb;
        }

        $qb->setParameter('className', $className);
        $qb->andWhere('cld.className = :className');

        if ($fieldName) {
            $qb->setParameter('fieldName', $fieldName);
            $qb->andWhere('cld.fieldName = :fieldName');
        } else {
            $qb->andWhere('cld.fieldName IS NULL');
        }

        if ($scope) {
            $qb->setParameter('scope', $scope);
            $qb->andWhere('cld.scope = :scope');
        }

        return $qb;
    }
}

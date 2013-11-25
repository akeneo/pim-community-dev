<?php

namespace Oro\Bundle\EntityConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class OptionSetRelationRepository extends EntityRepository
{
    /**
     * @param $fieldConfigId
     * @param array $values
     * @return array
     */
    public function findByNotIn($fieldConfigId, $values)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('a.field', $fieldConfigId),
                $qb->expr()->notIn('a.option', $values)
            )
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * returns the number of entity's rows
     * @param $fieldConfigId
     * @return int
     */
    public function count($fieldConfigId)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('COUNT(a)')
            ->where(
                $qb->expr()->eq('a.field', $fieldConfigId)
            );

        return $qb->getQuery()->getSingleScalarResult();
    }
}

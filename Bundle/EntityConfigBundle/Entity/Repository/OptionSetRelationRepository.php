<?php

namespace Oro\Bundle\EntityConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class OptionSetRelationRepository extends EntityRepository
{
    /**
     * @param int $fieldConfigId
     * @param int $entityId
     * @return array
     */
    public function findByFieldId($fieldConfigId, $entityId)
    {
        return $this->findBy(
            [
                'field'  => $fieldConfigId,
                'entity_id' => $entityId
            ]
        );
    }

    /**
     * @param int $fieldConfigId
     * @param int $entityId
     * @param array $values
     * @return array
     */
    public function findByNotIn($fieldConfigId, $entityId, $values)
    {
        if (empty($values)) {
            return $this->findByFieldId($fieldConfigId, $entityId);
        }

        $qb = $this->createQueryBuilder('a');
        $qb->where(
            'a.field = ?1  AND a.entity_id = ?2 AND a.option NOT IN (?3)'
        );
        $qb->setParameters(
            [
                1 => $fieldConfigId,
                2 => $entityId,
                3 => $values
            ]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * returns the number of entity's rows
     * @param int $fieldConfigId
     * @param $entityId
     * @return int
     */
    public function count($fieldConfigId, $entityId)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('COUNT(a)')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('a.field', $fieldConfigId),
                    $qb->expr()->eq('a.entity_id', $entityId)
                )
            );

        return $qb->getQuery()->getSingleScalarResult();
    }
}

<?php

namespace Oro\Bundle\TagBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\TagBundle\Entity\Taggable;

class TagRepository extends EntityRepository
{
    /**
     * Returns tags with taggings loaded by resource
     *
     * @param Taggable $resource
     * @param null $createdBy
     * @param bool $all
     * @return array
     */
    public function getTagging(Taggable $resource, $createdBy = null, $all = false)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t')
            ->innerJoin('t.tagging', 't2', Join::WITH, 't2.recordId = :recordId AND t2.entityName = :entityName')
            ->setParameter('recordId', $resource->getTaggableId())
            ->setParameter('entityName', get_class($resource));

        if (!is_null($createdBy)) {
            $qb->where('t2.createdBy ' . ($all ? '!=' : '=') . ' :createdBy')
                ->setParameter('createdBy', $createdBy);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Remove tagging related to tags by params
     *
     * @param array|int $tagIds
     * @param string $entityName
     * @param int $recordId
     * @param null|int $createdBy
     * @return array
     */
    public function deleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy = null)
    {
        $builder = $this->_em->createQueryBuilder();
        $builder
            ->delete('OroTagBundle:Tagging', 't')
            ->where($builder->expr()->in('t.tag', $tagIds))
            ->andWhere('t.entityName = :entityName')
            ->setParameter('entityName', $entityName)
            ->andWhere('t.recordId = :recordId')
            ->setParameter('recordId', $recordId);

        if (!is_null($createdBy)) {
            $builder->andWhere('t.createdBy = :createdBy')
                ->setParameter('createdBy', $createdBy);
        }

        return $builder->getQuery()->getResult();
    }
}

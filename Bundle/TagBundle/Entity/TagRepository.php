<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Common query-related methods for returning tag information
 *
 * @author Ryan Weaver <ryan@knplabs.com>
 */
class TagRepository extends EntityRepository
{
    /**
     * The field that's considered the "lookup" for tags
     *
     * @var string
     */
    protected $tagQueryField = 'name';

    /**
     * For a specific taggable type, this returns an array where they key
     * is the tag and the value is the number of times that tag is used
     *
     * @param string $taggableType The taggable type / resource type
     * @param null|integer $limit The max results to return
     * @return array
     */
    public function getTagsWithCountArray($taggableType, $limit = null)
    {
        $qb = $this->getTagsWithCountArrayQueryBuilder($taggableType);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $tags = $qb->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR)
        ;

        $arr = array();
        foreach ($tags as $tag) {
            $count = $tag['tag_count'];

            // don't include orphaned tags
            if ($count > 0) {
                $tagName = $tag[$this->tagQueryField];
                $arr[$tagName] = $count;
            }
        }

        return $arr;
    }

    /**
     * Returns an array of ids (e.g. Post ids) for a given taggable
     * type that have the given tag
     *
     * @param string $taggableType The type of object we're looking for
     * @param string $tag The actual tag we're looking for
     * @return array
     */
    public function getResourceIdsForTag($taggableType, $tag)
    {
        $results = $this->getTagsQueryBuilder($taggableType)
            ->andWhere('tag.'.$this->tagQueryField.' = :tag')
            ->setParameter('tag', $tag)
            ->select('tagging.resourceId')
            ->getQuery()
            ->execute(array(), AbstractQuery::HYDRATE_SCALAR)
        ;

        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['resourceId'];
        }

        return $ids;
    }

    /**
     * Returns a query builder built to return tag counts for a given type
     *
     * @see getTagsWithCountArray
     * @param $taggableType
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsWithCountArrayQueryBuilder($taggableType)
    {
        $qb = $this->getTagsQueryBuilder($taggableType)
            ->groupBy('tagging.tag')
            ->select('tag.'.$this->tagQueryField.', COUNT(tagging.tag) as tag_count')
            ->orderBy('tag_count', 'DESC')
        ;

        return $qb;
    }

    /**
     * Returns a query builder returning tags for a given type
     *
     * @param string $taggableType
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTagsQueryBuilder($taggableType)
    {
        return $this->createQueryBuilder('tag')
            ->join('tag.tagging', 'tagging')
            ->where('tagging.resourceType = :resourceType')
            ->setParameter('resourceType', $taggableType)
        ;
    }
}
<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\EntityManager;

class TagManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $tagClass;

    /**
     * @var string
     */
    protected $taggingClass;

    public function __construct(EntityManager $em, $tagClass, $taggingClass)
    {
        $this->em = $em;

        $this->tagClass = $tagClass;
        $this->taggingClass = $taggingClass;
    }

    /**
     * Deletes all tagging records for the given taggable resource
     *
     * @param Taggable $resource
     * @return $this
     */
    public function deleteTagging(Taggable $resource)
    {
        $taggingList = $this->em->createQueryBuilder()
            ->select('t')
            ->from($this->taggingClass, 't')

            ->where('t.entityName = :entityName')
            ->setParameter('entityName', get_class($resource))

            ->andWhere('t.recordId = :id')
            ->setParameter('id', $resource->getTaggableId())

            ->getQuery()
            ->getResult();

        foreach ($taggingList as $tagging) {
            $this->em->remove($tagging);
        }

        $this->em->flush($tagging);

        return $this;
    }
}

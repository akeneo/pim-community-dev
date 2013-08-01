<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\UserBundle\Acl\Manager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class TagManager
{
    const ACL_RESOURCE_ID_KEY = 'oro_tag_unassign_global';
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

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var Manager
     */
    protected $aclManager;

    public function __construct(
        EntityManager $em,
        $tagClass,
        $taggingClass,
        ObjectMapper $mapper,
        SecurityContextInterface $securityContext,
        Manager $aclManager
    ) {
        $this->em = $em;

        $this->tagClass = $tagClass;
        $this->taggingClass = $taggingClass;
        $this->mapper = $mapper;
        $this->securityContext = $securityContext;
        $this->aclManager = $aclManager;
    }

    /**
     * Adds a tag on the given taggable resource
     *
     * @param Tag      $tag      Tag object
     * @param Taggable $resource Taggable resource
     */
    public function addTag(Tag $tag, Taggable $resource)
    {
        $resource->getTags()->add($tag);
    }

    /**
     * Adds multiple tags on the given taggable resource
     *
     * @param Tag[]    $tags     Array of Tag objects
     * @param Taggable $resource Taggable resource
     */
    public function addTags($tags, Taggable $resource)
    {
        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $this->addTag($tag, $resource);
            }
        }
    }

    /**
     * Removes an existant tag on the given taggable resource
     *
     * @param  Tag      $tag      Tag object
     * @param  Taggable $resource Taggable resource
     * @return Boolean
     */
    public function removeTag(Tag $tag, Taggable $resource)
    {
        return $resource->getTags()->removeElement($tag);
    }

    /**
     * Loads or creates multiples tags from a list of tag names
     *
     * @param  array $names Array of tag names
     * @return Tag[]
     */
    public function loadOrCreateTags(array $names)
    {
        if (empty($names)) {
            return array();
        }

        $names = array_unique($names);

        $builder = $this->em->createQueryBuilder();

        $tags = $builder
            ->select('t')
            ->from($this->tagClass, 't')

            ->where($builder->expr()->in('t.name', $names))

            ->getQuery()
            ->getResult();

        $loadedNames = array();
        foreach ($tags as $tag) {
            $loadedNames[] = $tag->getName();
        }

        $missingNames = array_udiff($names, $loadedNames, 'strcasecmp');
        if (sizeof($missingNames)) {
            foreach ($missingNames as $name) {
                $tag = $this->createTag($name);
                $this->em->persist($tag);

                $tags[] = $tag;
            }

            $this->em->flush();
        }

        return $tags;
    }

    /**
     * Saves tags for the given taggable resource
     *
     * @param Taggable $resource Taggable resource
     */
    public function saveTagging(Taggable $resource)
    {
        $oldTags = $this->getTagging($resource, $this->getUser()->getId());
        $newTags = $resource->getTags();

        if (!isset($newTags['all'], $newTags['owner'])) {
            return;
        }

        // allow adding only 'my' tags
        $newOwnerTags = new ArrayCollection($newTags['owner']);

        // find new
        $tagsToAdd = new ArrayCollection();
        foreach ($newOwnerTags as $newOwnerTag) {
            $callback = function ($index, $oldTag) use ($newOwnerTag) {
                return $oldTag->getName() == $newOwnerTag->getName();
            };

            if (!$oldTags->exists($callback)) {
                $tagsToAdd->add($newOwnerTag);
            }
        }

        // find removed
        $tagsToRemove = array();
        foreach ($oldTags as $oldTag) {
            $callback = function ($index, $newTag) use ($oldTag) {
                return $newTag->getName() == $oldTag->getName();
            };

            if (!$newOwnerTags->exists($callback)) {
                $tagsToRemove[] = $oldTag->getId();
            }
        }

        if (sizeof($tagsToRemove)) {
            $this->deleteTaggingByParams(
                $tagsToRemove,
                get_class($resource),
                $resource->getTaggableId(),
                $this->getUser()->getId()
            );
        }

        // process if current user allowed to remove other's tag links
        if ($this->aclManager->isResourceGranted(self::ACL_RESOURCE_ID_KEY)) {
            $newAllTags = new ArrayCollection($newTags['all']);
            // get 'not mine' taggings
            $oldTags = $this->getTagging($resource, $this->getUser()->getId(), true);
            $tagsToRemove = array();

            foreach ($oldTags as $oldTag) {
                $callback = function ($index, $newTag) use ($oldTag) {
                    return $newTag->getName() == $oldTag->getName();
                };

                if (!$newAllTags->exists($callback)) {
                    $tagsToRemove[] = $oldTag->getId();
                }
            }

            if (count($tagsToRemove) > 0) {
                $this->deleteTaggingByParams(
                    $tagsToRemove,
                    get_class($resource),
                    $resource->getTaggableId()
                );
            }
        }

        foreach ($tagsToAdd as $tag) {
            $this->em->persist($tag);

            $alias = $this->mapper->getEntityConfig(get_class($resource));

            $tagging = $this->createTagging($tag, $resource)
                ->setAlias($alias['alias']);

            $this->em->persist($tagging);
        }

        if (count($tagsToAdd)) {
            $this->em->flush();
        }
    }

    /**
     * Loads all tags for the given taggable resource
     *
     * @param Taggable $resource Taggable resource
     */
    public function loadTagging(Taggable $resource)
    {
        $tags = $this->getTagging($resource);
        $resource->setTags(new ArrayCollection());
        $this->addTags($tags, $resource);
    }

    /**
     * Gets all tags for the given taggable resource
     *
     * @param  Taggable $resource Taggable resource
     * @param null|int $createdBy
     * @param bool $all
     * @return array
     */
    protected function getTagging(Taggable $resource, $createdBy = null, $all = false)
    {
        $qb = $this->em
            ->createQueryBuilder()

            ->select('t')
            ->from($this->tagClass, 't')

            ->innerJoin('t.tagging', 't2', Join::WITH, 't2.recordId = :recordId AND t2.entityName = :entityName')
            ->setParameter('recordId', $resource->getTaggableId())
            ->setParameter('entityName', get_class($resource));

        if (!is_null($createdBy)) {
            $qb->where('t2.createdBy ' . ($all ? '!=' : '=') . ' :createdBy')
                ->setParameter('createdBy', $createdBy);
        }

        return new ArrayCollection($qb->getQuery()->getResult());
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
            $this->em->flush($tagging);
        }

        return $this;
    }

    /**
     * @param $tagIds
     * @param $entityName
     * @param $recordsId
     * @param null $createdBy
     */
    public function deleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy = null)
    {
        $builder = $this->em->createQueryBuilder();
        $builder
            ->delete($this->taggingClass, 't')
            ->where($builder->expr()->in('t.tag', $tagIds))
            ->andWhere('t.entityName = :entityName')
            ->setParameter('entityName', $entityName)
            ->andWhere('t.recordId = :recordId')
            ->setParameter('recordId', $recordId);

        if (!is_null($createdBy)) {
            $builder->andWhere('t.createdBy = :createdBy')
                    ->setParameter('createdBy', $createdBy);
        }

        $builder->getQuery()->getResult();
    }

    /**
     * Creates a new Tag object
     *
     * @param  string $name Tag name
     * @return Tag
     */
    protected function createTag($name)
    {
        return new $this->tagClass($name);
    }

    /**
     * Creates a new Tagging object
     *
     * @param  Tag      $tag      Tag object
     * @param  Taggable $resource Taggable resource object
     * @return Tagging
     */
    protected function createTagging(Tag $tag, Taggable $resource)
    {
        return new $this->taggingClass($tag, $resource);
    }

    /**
     * Return current user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->securityContext->getToken()->getUser();
    }
}

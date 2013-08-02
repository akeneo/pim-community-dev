<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

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

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    public function __construct(EntityManager $em, $tagClass, $taggingClass, ObjectMapper $mapper, SecurityContextInterface $securityContext)
    {
        $this->em = $em;

        $this->tagClass = $tagClass;
        $this->taggingClass = $taggingClass;
        $this->mapper = $mapper;
        $this->securityContext = $securityContext;
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
    public function addTags(array $tags, Taggable $resource)
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
     * Replaces all current tags on the given taggable resource
     *
     * @param Tag[]    $tags     Array of Tag objects
     * @param Taggable $resource Taggable resource
     */
    public function replaceTags(array $tags, Taggable $resource)
    {
        $resource->setTags(new ArrayCollection());

        $this->addTags($tags, $resource);
    }

    /**
     * Loads or creates multiples tags from a list of tag names
     *
     * @param array $names Array of tag names
     * @param boolean $needFlush
     * @return Tag[]
     */
    public function loadOrCreateTags(array $names, $needFlush = true)
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

            if ($needFlush) {
                $this->em->flush();
            }
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
        $oldTags = $this->getTagging($resource);
        $newTags = $resource->getTags();

        if (!isset($newTags['all'], $newTags['owner'])) {
            return;
        }

        $tagsToAdd = new ArrayCollection($newTags['owner']);

        if ($oldTags !== null and is_array($oldTags) and !empty($oldTags)) {
            $tagsToRemove = array();

            foreach ($oldTags as $oldTag) {
                $callback = function ($index, $newTag) use ($oldTag) {
                    return $newTag->getName() == $oldTag->getName();
                };

                if ($tagsToAdd->exists($callback)) {
                    $tagsToAdd->removeElement($oldTag);
                } else {
                    $tagsToRemove[] = $oldTag->getId();
                }
            }

            if (sizeof($tagsToRemove)) {
                $builder = $this->em->createQueryBuilder();
                $builder
                    ->delete($this->taggingClass, 't')
                    ->where($builder->expr()->in('t.tag', $tagsToRemove))
                    ->andWhere('t.entityName = :entityName')
                    ->setParameter('entityName', get_class($resource))
                    ->andWhere('t.recordId = :recordId')
                    ->setParameter('recordId', $resource->getTaggableId())
                    ->getQuery()
                    ->getResult();
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
        $this->replaceTags($tags, $resource);
    }

    /**
     * Gets all tags for the given taggable resource
     *
     * @param  Taggable $resource Taggable resource
     * @return array
     */
    protected function getTagging(Taggable $resource)
    {
        $query = $this->em
            ->createQueryBuilder()

            ->select('t')
            ->from($this->tagClass, 't')

            ->innerJoin('t.tagging', 't2', Join::WITH, 't2.recordId = :recordId AND t2.entityName = :entityName')
            ->setParameter('recordId', $resource->getTaggableId())
            ->setParameter('entityName', get_class($resource))
            ->getQuery();

        return $query->getResult();
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
}

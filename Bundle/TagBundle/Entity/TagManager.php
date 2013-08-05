<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\TagBundle\Entity\Repository\TagRepository;

class TagManager
{
    const ACL_RESOURCE_REMOVE_ID_KEY = 'oro_tag_unassign_global';
    const ACL_RESOURCE_CREATE_ID_KEY = 'oro_tag_create';
    const ACL_RESOURCE_ASSIGN_ID_KEY = 'oro_tag_assign_unassign';
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

    /**
     * @var Router
     */
    protected $router;

    public function __construct(
        EntityManager $em,
        $tagClass,
        $taggingClass,
        ObjectMapper $mapper,
        SecurityContextInterface $securityContext,
        Manager $aclManager,
        Router $router
    ) {
        $this->em = $em;

        $this->tagClass = $tagClass;
        $this->taggingClass = $taggingClass;
        $this->mapper = $mapper;
        $this->securityContext = $securityContext;
        $this->aclManager = $aclManager;
        $this->router = $router;
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
                $resource->getTags()->add($tag);
            }
        }
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
        $tags = $this->em->getRepository($this->tagClass)->findBy(array('name' =>  $names));

        $loadedNames = array();
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $loadedNames[] = $tag->getName();
        }

        $missingNames = array_udiff($names, $loadedNames, 'strcasecmp');
        if (sizeof($missingNames)) {
            foreach ($missingNames as $name) {
                $tag = $this->createTag($name);

                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * Prepare array
     *
     * @param Taggable $entity
     * @param ArrayCollection|null $tags
     * @return array
     */
    public function getPreparedArray(Taggable $entity, $tags = null)
    {
        if (is_null($tags)) {
            $this->loadTagging($entity);
            $tags = $entity->getTags();
        }
        $result = array();

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $entry = array(
                'name' => $tag->getName(),
                'id'   => $tag->getId() ? $tag->getId() : $tag->getName(),
                'url'  => $tag->getId()
                    ? $this->router->generate('oro_tag_search', array('id' => $tag->getId()))
                    : false,
                'owner' => false
            );

            $taggingCollection = $tag->getTagging()->filter(
                function (Tagging $tagging) use ($entity) {
                    // only use tagging entities that related to current entity
                    return $tagging->getEntityName() == get_class($entity)
                    && $tagging->getRecordId() == $entity->getTaggableId();
                }
            );
            /** @var Tagging $tagging */
            foreach ($taggingCollection as $tagging) {
                if ($this->getUser()->getId() == $tagging->getCreatedBy()->getId()) {
                    $entry['owner'] = true;
                }
            }

            $entry['moreOwners'] = $taggingCollection->count() > 1;
            $entry['owner'] = $tag->getId() ? $entry['owner'] : true;

            $result[] = $entry;
        }

        return $result;
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
        if ($this->aclManager->isResourceGranted(self::ACL_RESOURCE_REMOVE_ID_KEY)) {
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
            if (
                !$this->aclManager->isResourceGranted(self::ACL_RESOURCE_ASSIGN_ID_KEY)
                || (!$this->aclManager->isResourceGranted(self::ACL_RESOURCE_CREATE_ID_KEY) && !$tag->getId())
            ) {
                // skip tags that have not ID because user not granted to create tags
                continue;
            }

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
     * @return $this
     */
    public function loadTagging(Taggable $resource)
    {
        $tags = $this->getTagging($resource);
        $this->addTags($tags, $resource);

        return $this;
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
        /** @var TagRepository $repository */
        $repository = $this->em->getRepository($this->tagClass);

        return new ArrayCollection($repository->getTagging($resource, $createdBy, $all));
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
        /** @var TagRepository $repository */
        $repository = $this->em->getRepository($this->tagClass);

        if (!$tagIds) {
            $tagIds = array();
        }

        return $repository->deleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy);
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
     * @return User
     */
    public function getUser()
    {
        return $this->securityContext->getToken()->getUser();
    }
}

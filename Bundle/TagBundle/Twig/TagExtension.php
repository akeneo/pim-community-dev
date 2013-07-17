<?php

namespace Oro\Bundle\TagBundle\Twig;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\Tagging;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\TagBundle\Entity\TagManager;

class TagExtension extends \Twig_Extension
{
    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     */
    protected $manager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var SecurityContextInterface
     */
    protected $context;

    /**
     * @var \Oro\Bundle\UserBundle\Acl\ManagerInterface
     */
    private $aclManager;

    public function __construct(TagManager $manager, Router $router, SecurityContextInterface $securityContext, ManagerInterface $aclManager)
    {
        $this->manager    = $manager;
        $this->router     = $router;
        $this->aclManager = $aclManager;

        $this->context    = $securityContext;
    }

    /**
     * Return current user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->context->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'oro_tag_get_list' => new \Twig_Function_Method($this, 'get')
        );
    }

    /**
     * Return array of tags
     *
     * @param  Taggable $entity
     * @return array
     */
    public function get(Taggable $entity)
    {
        $this->manager->loadTagging($entity);
        $result = array();

        /** @var Tag $tag */
        foreach ($entity->getTags() as $tag) {
            $entry = array(
                'name' => $tag->getName(),
                'id'   => $tag->getId(),
                'url'  => $this->router->generate('oro_tag_search', array('id' => $tag->getId()))
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

            if (!$this->aclManager->isResourceGranted('oro_tag_unassign_global') && !isset($entry['owner'])) {
                $entry['locked'] = true;
            }

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_tag';
    }
}

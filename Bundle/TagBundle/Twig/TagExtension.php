<?php

namespace Oro\Bundle\TagBundle\Twig;

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

    public function __construct(TagManager $manager, Router $router, SecurityContextInterface $securityContext)
    {
        $this->manager = $manager;
        $this->router = $router;

        $this->context = $securityContext;
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

            /** @var Tagging $tagging */
            foreach ($tag->getTagging() as $tagging) {
                if ($this->getUser()->getId() == $tagging->getUser()->getId()) {
                    $entry['owner'] = true;
                }
            }

            if (!$this->context->isGranted('oro_tag_unassign', $tag)) {
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

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
     * @param Taggable $entity
     * @return array
     */
    public function get(Taggable $entity)
    {
        $this->manager->loadTagging($entity);

        // we have to keep all own tags and only one from another users
        $result = array();
        $keys = array();
        /** @var Tag $tag */
        foreach ($entity->getTags() as $tag) {
            $slug = $this->slugify($tag->getName());

            if (!isset($keys[$slug])) {
                $result[] = array(
                    'text' => $tag->getName(),
                    'id'   => $tag->getId(),
                    'url'  => $this->router->generate('oro_tag_search', array('id' => $tag->getId()))
                );
            }

            /** @var Tagging $tagging */
            foreach ($tag->getTagging() as $tagging) {
                if ($this->getUser()->getId() == $tagging->getUser()->getId()) {
                    $current = end($result);
                    $current['owner'] = true;
                    $result[count($result) - 1] = $current;
                }
            }
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

    /**
     * Return slug
     *
     * @param string $name
     * @return string
     */
    private function slugify($name)
    {
        $slug = mb_convert_case($name, MB_CASE_LOWER, mb_detect_encoding($name));
        $slug = str_replace(' ', '-', $slug);
        $slug = str_replace('--', '-', $slug);

        return $slug;
    }
}

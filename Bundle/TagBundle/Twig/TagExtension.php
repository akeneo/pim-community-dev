<?php

namespace Oro\Bundle\TagBundle\Twig;

use Symfony\Component\Routing\Router;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\TagBundle\Entity\TagManager;

class TagExtension extends \Twig_Extension
{
    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     */
    protected $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager    = $manager;
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
        return $this->manager->getPreparedArray($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_tag';
    }
}

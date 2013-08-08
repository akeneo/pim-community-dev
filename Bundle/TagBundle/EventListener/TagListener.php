<?php

namespace Oro\Bundle\TagBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\TagBundle\Entity\Taggable;

/**
 * TagListener.
 */
class TagListener implements ContainerAwareInterface
{
    protected $manager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        if (is_null($this->manager) && $this->container) {
            $this->manager = $this->container->get('oro_tag.tag.manager');
        }

        if (($resource = $args->getEntity()) and $resource instanceof Taggable) {
            $this->manager->deleteTaggingByParams(
                null,
                get_class($resource),
                $resource->getTaggableId()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

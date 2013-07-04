<?php

namespace Oro\Bundle\TagBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TagListener.
 *
 */
class TagListener implements EventSubscriber, ContainerAwareInterface
{
    protected $manager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @see Doctrine\Common\EventSubscriber
     */
    public function getSubscribedEvents()
    {
        return array(Events::preRemove);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        if (is_null($this->manager)) {
            $this->manager = $this->container->get('fpn_tag.tag_manager');
        }

        if (($resource = $args->getEntity()) and $resource instanceof Taggable) {
            $this->manager->deleteTagging($resource);
        }
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
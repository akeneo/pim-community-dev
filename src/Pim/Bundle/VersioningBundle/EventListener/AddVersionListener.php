<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Pim\Bundle\ProductBundle\Model\ProductValueInterface;

use Pim\Bundle\ProductBundle\Model\ProductInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\ConfigBundle\Entity\Channel;
use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Aims to audit data updates on product, attribute, family, category
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AddVersionListener implements EventSubscriber
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return ScopableListener
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ProductInterface) {
die('toto');
        }
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ProductValueInterface) {
            die('titi');
        }
    }
}

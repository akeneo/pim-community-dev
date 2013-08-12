<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Pim\Bundle\ProductBundle\Entity\Family;

use Pim\Bundle\ProductBundle\Model\ProductValueInterface;

use Pim\Bundle\ProductBundle\Model\ProductInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\ConfigBundle\Entity\Channel;
use Pim\Bundle\ConfigBundle\Entity\Locale;

use Pim\Bundle\VersioningBundle\Model\Versionable;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Doctrine\ORM\Event\OnFlushEventArgs;

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
                /*
            'prePersist',
            'preUpdate',
            */
            'onFlush'
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
//die('toto');
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

        if ($entity instanceof Family) {
            //die('titi');
        }
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() AS $entity) {
            if ($entity instanceof Versionable) {
                $this->_makeSnapshot($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            if ($entity instanceof Versionable) {
                $this->_makeSnapshot($entity);
            }
        }
    }

    /**
     * @param VersionableInterface $entity
     */
    private function _makeSnapshot(Versionable $entity)
    {
        return;
        var_dump($entity->getVersionedData());

        die();
        $resourceVersion = new Version($entity);
        $class = $this->_em->getClassMetadata(get_class($resourceVersion));

        $this->_em->persist($resourceVersion);
        $this->_em->getUnitOfWork()->computeChangeSet($class, $resourceVersion);
    }
}

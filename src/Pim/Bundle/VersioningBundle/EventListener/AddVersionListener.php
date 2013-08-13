<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

use Pim\Bundle\ProductBundle\Entity\ProductPrice;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Pim\Bundle\VersioningBundle\Model\Versionable;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;

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
     * Versions to save
     * @var array
     */
    protected $pendingVersions = array();

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
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
                $this->writeSnapshot($em, $entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {

            if ($entity instanceof Versionable) {
                $this->writeSnapshot($em, $entity);

            } else if($entity instanceof ProductValueInterface) {
                 $product = $entity->getEntity();
                 $this->writeSnapshot($em, $product);

            } else if ($entity instanceof ProductPrice) {
                 $product = $entity->getValue()->getEntity();
                 $this->writeSnapshot($em, $product);

            } else if ($entity instanceof AbstractTranslation) {
                if ($entity->getForeignKey() instanceof Versionable) {
                    $this->writeSnapshot($em, $entity->getForeignKey());
                }
            }
        }
    }

    /**
     * Write snapshot
     *
     * @param EntityManager        $em
     * @param VersionableInterface $entity
     */
    protected function writeSnapshot(EntityManager $em, Versionable $entity)
    {
        $oid = spl_object_hash($entity);
        if (!isset($this->pendingVersions[$oid])) {
            $version = new Version($entity);
            $this->computeChangeSet($em, $version);
            $this->pendingVersions[$oid]= $version;
        }
    }

    /**
     * Compute change set
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    protected function computeChangeSet(EntityManager $em, $entity)
    {
        $class = $em->getClassMetadata(get_class($entity));
        $em->persist($entity);
        $em->getUnitOfWork()->computeChangeSet($class, $entity);
    }
}

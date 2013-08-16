<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\ProductBundle\Manager\AuditManager;
use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;

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
     * Version builder
     * @var VersionBuilder
     */
    protected $builder;

    /**
     * @param VersionBuilder $builder
     */
    public function __construct(VersionBuilder $builder)
    {
        $this->builder = $builder;
    }

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
            // TODO add same coverage than update, pb with unexisting entity id
        }

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {

            if ($entity instanceof VersionableInterface) {
                $this->writeSnapshot($em, $entity);

            } else if($entity instanceof ProductValueInterface) {
                 $product = $entity->getEntity();
                 if ($product) {
                     $this->writeSnapshot($em, $product);
                 }

            } else if ($entity instanceof ProductPrice) {
                 $product = $entity->getValue()->getEntity();
                 $this->writeSnapshot($em, $product);

            } else if ($entity instanceof AbstractTranslation) {
                $translatedEntity = $entity->getForeignKey();
                if ($translatedEntity instanceof VersionableInterface) {
                    $this->writeSnapshot($em, $translatedEntity);
                }
            }
        }

        foreach ($uow->getScheduledCollectionDeletions() AS $entity) {
            if ($entity->getOwner() instanceof VersionableInterface) {
                $this->writeSnapshot($em, $entity->getOwner());
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() AS $entity) {
            if ($entity->getOwner() instanceof VersionableInterface) {
                // special case for product to category
                $this->writeSnapshot($em, $entity->getOwner());
            }
        }
    }

    /**
     * Write snapshot
     *
     * @param EntityManager        $em
     * @param VersionableInterfaceInterface $entity
     */
    public function writeSnapshot(EntityManager $em, VersionableInterface $versionable)
    {
        $oid = spl_object_hash($versionable);
        if (!isset($this->pendingVersions[$oid]) and $versionable->getId() !== null) {

            /** @var User $user */
            $user = $em->getRepository('OroUserBundle:User')
                ->findOneBy(array('username' => 'admin')); // TODO : to fix !

            $version  = $this->builder->buildVersion($versionable, $user);
            $previous = $em->getRepository('PimVersioningBundle:Version')
                ->findOneBy(array('resourceId' => $version->getResourceId()), array('snapshotDate' => 'desc'));

            $audit = $this->builder->buildAudit($version, $previous);
            $diffData = $audit->getData();

            if (!empty($diffData)) {
                $this->computeChangeSet($em, $audit);
                $this->computeChangeSet($em, $version);
                $this->pendingVersions[$oid]= $version;
            }
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

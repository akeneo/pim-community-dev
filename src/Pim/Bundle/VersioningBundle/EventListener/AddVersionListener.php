<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\ProductBundle\Manager\AuditManager;
use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;
use Pim\Bundle\ProductBundle\Model\CategoryInterface;
use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\AttributeOptionValue;

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
     * Entities to version
     *
     * @var array
     */
    protected $pendingEntities;

    /**
     * Version builder
     * @var VersionBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $username;

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
        return array('onFlush', 'postFlush');
    }

    /**
     * @param $username
     *
     * @throws \InvalidArgumentException
     */
    public function setUsername($username)
    {
        if (is_string($username)) {
            $this->username = $username;
        } elseif (is_object($username) && method_exists($username, 'getUsername')) {
            $this->username = (string) $username->getUsername();
        } else {
            throw new \InvalidArgumentException("Username must be a string, or object should have method: getUsername");
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->pendingEntities)) {
            $em   = $args->getEntityManager();
            $user = $this->getUser($em);
            if ($user) {
                foreach ($this->pendingEntities as $oid => $versionable) {
                    $this->writeSnapshot($em, $versionable, $user);
                }
                $this->pendingEntities = array();
            }
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
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() AS $entity) {
            $this->checkScheduledCollection($em, $entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() AS $entity) {
            $this->checkScheduledCollection($em, $entity);
        }
    }

    /**
     * Check if an entity must be versioned due to entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    public function checkScheduledUpdate(EntityManager $em, $entity)
    {
        if ($entity instanceof VersionableInterface) {
            $this->addPendingVersioning($entity);

        } else if($entity instanceof ProductValueInterface) {
            $product = $entity->getEntity();
            $this->addPendingVersioning($product);

        } else if ($entity instanceof ProductPrice) {
            $product = $entity->getValue()->getEntity();
            $this->addPendingVersioning($product);

        } else if ($entity instanceof AbstractTranslation) {
            $translatedEntity = $entity->getForeignKey();
            if ($translatedEntity instanceof VersionableInterface) {
                $this->addPendingVersioning($translatedEntity);
            }

        } else if ($entity instanceof AttributeOption) {
            $attribute = $entity->getAttribute();
            $this->addPendingVersioning($attribute);

        } else if ($entity instanceof AttributeOptionValue) {
            $attribute = $entity->getOption()->getAttribute();
            $this->addPendingVersioning($attribute);
        }
    }

    /**
     * Check if an entity must be versioned due to collection changes
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    public function checkScheduledCollection(EntityManager $em, $entity)
    {
        if ($entity->getOwner() instanceof VersionableInterface) {
            // TODO : special case, when the product collection of a category is updated, we update each product
            /*
            if ($entity->getOwner() instanceof CategoryInterface) {
                $mapping = $entity->getMapping();
                if (isset($mapping['fieldName']) and $mapping['fieldName'] == 'products') {
                    foreach ($entity->getInsertDiff() as $product) {
                        $this->addPendingVersioning($product);
                    }
                    foreach ($entity->getDeleteDiff() as $product) {
                        $this->addPendingVersioning($product);
                    }
                }
            }*/
            $this->addPendingVersioning($entity->getOwner());
        }
    }

    /**
     * Mark entity as to be versioned
     *
     * @param VersionableInterface $versionable
     */
    protected function addPendingVersioning(VersionableInterface $versionable)
    {
        $oid = spl_object_hash($versionable);
        if (!isset($this->pendingEntities[$oid])) {
            $this->pendingEntities[$oid] = $versionable;
        }
    }

    /**
     * Write snapshot
     *
     * @param EntityManager        $em
     * @param VersionableInterface $entity
     * @param User                 $user
     */
    public function writeSnapshot(EntityManager $em, VersionableInterface $versionable, User $user)
    {
        $version  = $this->buildVersion($versionable, $user);
        $previous = $this->getPreviousVersion($em, $version);
        $audit    = $this->buildAudit($version, $previous);
        $diffData = $audit->getData();

        if (!empty($diffData)) {
            $em->persist($version);
            $em->persist($audit);
            $em->flush();
        }
    }

    /**
     * @param VersionableInterface $versionable
     * @param User                 $user
     *
     * @return Version
     */
    protected function buildVersion(VersionableInterface $versionable, User $user)
    {
        return $this->builder->buildVersion($versionable, $user);
    }

    /**
     * @param Version $version
     * @param Version $previous
     *
     * @return Audit
     */
    protected function buildAudit(Version $version, Version $previous = null)
    {
        return $this->builder->buildAudit($version, $previous);
    }

    /**
     * @param EntityManager $em
     *
     * @return User
     */
    protected function getUser(EntityManager $em)
    {
        /** @var User $user */
        $user = $em->getRepository('OroUserBundle:User')->findOneBy(array('username' => $this->username));

        return $user;
    }

    /**
     * @param EntityManager $em
     *
     * @return Version
     */
    protected function getPreviousVersion(EntityManager $em, Version $version)
    {
        /** @var Version $version */
        $previous = $em->getRepository('PimVersioningBundle:Version')
            ->findOneBy(
                array('resourceId' => $version->getResourceId(), 'resourceName' => $version->getResourceName()),
                array('snapshotDate' => 'desc')
            );

        return $previous;
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

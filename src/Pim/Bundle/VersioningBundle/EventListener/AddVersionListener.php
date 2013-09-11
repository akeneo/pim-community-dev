<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Aims to audit data updates on product, attribute, family, category
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionListener implements EventSubscriber
{
    /**
     * Default system user, used for batches
     *
     * @var string
     */
    const DEFAULT_SYSTEM_USER = 'admin';

    /**
     * Entities to version
     *
     * @var VersionableInterface[]
     */
    protected $pendingEntities;

    /**
     * @var string
     */
    protected $username = self::DEFAULT_SYSTEM_USER;

    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('onFlush', 'postFlush');
    }

    /**
     * @param mixed $username
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
        $em = $args->getEntityManager();

        if (!empty($this->pendingEntities)) {
            if ($this->username) {
                foreach ($this->pendingEntities as $versionable) {
                    if ($versionable->getId()) {
                        $pending = new Pending(get_class($versionable), $versionable->getId(), $this->username);
                        $this->computeChangeSet($em, $pending);
                    }
                }
                $this->pendingEntities = array();
                $em->flush();
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

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->checkScheduledCollection($em, $entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->checkScheduledCollection($em, $entity);
        }
    }

    /**
     * Check if an entity must be versioned due to entity changes
     *
     * @param object $entity
     */
    public function checkScheduledUpdate($em, $entity)
    {
        if ($entity instanceof ProductAttribute) {
            $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity);
            if (in_array('group', array_keys($changeset)) and $entity->getGroup()) {
                $this->addPendingVersioning($em, $entity->getGroup());
            }

        } elseif ($entity instanceof VersionableInterface) {
            $this->addPendingVersioning($em, $entity);

        } elseif ($entity instanceof ProductValueInterface) {
            $product = $entity->getEntity();
            if ($product) {
                $this->addPendingVersioning($em, $product);
            }

        } elseif ($entity instanceof ProductPrice) {
            $product = $entity->getValue()->getEntity();
            $this->addPendingVersioning($em, $product);

        } elseif ($entity instanceof AbstractTranslation) {
            $translatedEntity = $entity->getForeignKey();
            if ($translatedEntity instanceof VersionableInterface) {
                $this->addPendingVersioning($em, $translatedEntity);
            }

        } elseif ($entity instanceof AttributeOption) {
            $attribute = $entity->getAttribute();
            $this->addPendingVersioning($em, $attribute);

        } elseif ($entity instanceof AttributeOptionValue) {
            $attribute = $entity->getOption()->getAttribute();
            $this->addPendingVersioning($em, $attribute);
        }
    }

    /**
     * Check if an entity must be versioned due to collection changes
     *
     * @param object $entity
     */
    public function checkScheduledCollection($em, $entity)
    {
        if ($entity->getOwner() instanceof VersionableInterface) {
            $this->addPendingVersioning($em, $entity->getOwner());
        }
    }

    /**
     * Mark entity as to be versioned
     *
     * @param VersionableInterface $versionable
     */
    protected function addPendingVersioning($em, VersionableInterface $versionable)
    {
        $oid = spl_object_hash($versionable);
        if (!isset($this->pendingEntities[$oid])) {
            if (!$versionable->getId()) {
                $this->pendingEntities[$oid] = $versionable;
            } else {
                $pending = new Pending(get_class($versionable), $versionable->getId(), $this->username);
                $this->computeChangeSet($em, $pending);
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

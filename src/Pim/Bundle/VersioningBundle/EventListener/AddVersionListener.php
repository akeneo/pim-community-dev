<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Model\Versionable;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

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
            // TODO add same coverage than update, pb with unexisting entity id
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
    public function writeSnapshot(EntityManager $em, Versionable $versionable)
    {
        $oid = spl_object_hash($versionable);
        if (!isset($this->pendingVersions[$oid])) {

            $version = new Version($versionable);
            $this->computeChangeSet($em, $version);
            $this->pendingVersions[$oid]= $version;

            /** @var User $user */
            $user = $em->getRepository('OroUserBundle:User')
                ->findOneBy(array('username' => 'admin')); // TODO : to fix !

            $logEntry = new Audit();
            $logEntry->setAction('update'); // TODO create if there is no
            $logEntry->setObjectClass($version->getResourceName());
            $logEntry->setLoggedAt();
            $logEntry->setUser($user);
            $logEntry->setObjectName('TODO : useless name ?');
            $logEntry->setObjectId($version->getResourceId());
            $logEntry->setVersion($version->getVersion());
            $newData = $version->getVersionedData();

            $previousVersion = $em->getRepository('PimVersioningBundle:Version')
                ->findOneBy(array('resourceId' => $version->getResourceId()), array('version' => 'desc'));

            if ($previousVersion) {
                $oldData = $previousVersion->getVersionedData();
            } else {
                $oldData = array();
            }

            $diff = array_diff($newData, $oldData);
            $diffData = array();
            foreach (array_keys($diff) as $changedField) {
                if (isset($oldData[$changedField])) {
                    $diffData[$changedField]= array('old' => $oldData[$changedField]);
                } else {
                    $diffData[$changedField]= array('old' => '');
                }
                if (isset($newData[$changedField])) {
                    $diffData[$changedField]['new'] = $newData[$changedField];
                } else {
                    $diffData[$changedField]['new'] = '';
                }
            }

            $logEntry->setData($diffData);

            if (!empty($diffData)) {
                $this->computeChangeSet($em, $logEntry);
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

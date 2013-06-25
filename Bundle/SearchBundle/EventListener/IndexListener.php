<?php

namespace Oro\Bundle\SearchBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

class IndexListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $realtime;

    /**
     * @var array
     */
    protected $entities;

    protected $pendingInserts = array();

    /**
     * Unfortunately, can't use AbstractEngine as a parameter here due to circular reference
     *
     * @param ContainerInterface $container
     * @param bool               $realtime  Realtime update flag
     * @param array              $entities  Entities config array from search.yml
     */
    public function __construct(ContainerInterface $container, $realtime, $entities)
    {
        $this->container = $container;
        $this->realtime  = $realtime;
        $this->entities  = $entities;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        if (empty($this->entities)) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (isset($this->entities[get_class($entity)])) {
                $this->pendingInserts[spl_object_hash($entity)] = $this->container->get('oro_search.search.engine')->save($entity, $this->realtime, true);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (isset($this->entities[get_class($entity)])) {
                $this->container->get('oro_search.search.engine')->save($entity, $this->realtime, true);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (isset($this->entities[get_class($entity)])) {
                $this->container->get('oro_search.search.engine')->delete($entity, $this->realtime);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $oid = spl_object_hash($entity);
        $em = $args->getEntityManager();

        if (array_key_exists($oid, $this->pendingInserts) ) {
            $searchEntity     = $this->pendingInserts[$oid];

            $entityMeta      = $em->getClassMetadata(get_class($entity));
            $identifierField = $entityMeta->getSingleIdentifierFieldName($entityMeta);
            $id =  $entityMeta->getReflectionProperty($identifierField)->getValue($entity);
            $searchEntity->setRecordId($id);

            $em->persist($searchEntity);
            unset($this->pendingInserts[$oid]);
            $em->flush($searchEntity);
        }
    }
}

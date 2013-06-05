<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class EntityResultListener
{
    /**
     * @var string
     */
    protected $datagridName;

    /**
     * @var RegistryInterface
     */
    protected $doctrineRegistry;

    /**
     * @param RegistryInterface $doctrineRegistry
     * @param string            $datagridName
     */
    public function __construct(RegistryInterface $doctrineRegistry, $datagridName)
    {
        $this->doctrineRegistry = $doctrineRegistry;
        $this->datagridName     = $datagridName;
    }

    /**
     * @param ResultDatagridEvent $event
     */
    public function processResult(ResultDatagridEvent $event)
    {
        if (!$event->isDatagridName($this->datagridName)) {
            return;
        }

        $rows = $event->getRows();
        $entities = array();

        // group entities by type
        /** @var $row Item */
        foreach ($rows as $row) {
            $entityName = $row->getEntityName();
            $entities[$entityName][] = $row->getRecordId();
        }

        // get actual entities
        foreach ($entities as $entityName => $entityIds) {
            $entities[$entityName] = $this->getEntities($entityName, $entityIds);
        }

        // add entities to result rows
        $resultRows = array();
        foreach ($rows as $row) {
            $entity     = null;
            $entityName = $row->getEntityName();
            $entityId   = $row->getRecordId();
            if (isset($entities[$entityName][$entityId])) {
                $entity = $entities[$entityName][$entityId];
            }

            $resultRows[] = array(
                'indexer_item' => $row,
                'entity' => $entity,
            );
        }

        $event->setRows($resultRows);
    }

    /**
     * @param  string        $entityName
     * @return ClassMetadata
     */
    protected function getEntityMetadata($entityName)
    {
        /** @var $entityManager EntityManager */
        $entityManager = $this->doctrineRegistry->getManager();

        return $entityManager->getMetadataFactory()->getMetadataFor($entityName);
    }

    /**
     * @param  string $entityName
     * @return string
     */
    protected function getEntityIdentifier($entityName)
    {
        $idFields = $this->getEntityMetadata($entityName)->getIdentifierFieldNames();

        return current($idFields);
    }

    /**
     * @param  string $entityName
     * @param  array  $entityIds
     * @return array
     */
    protected function getEntities($entityName, array $entityIds)
    {
        /** @var $entityManager EntityManager */
        $entityManager = $this->doctrineRegistry->getManager();
        $classMetadata = $this->getEntityMetadata($entityName);
        $idField = $this->getEntityIdentifier($entityName);

        $queryBuilder = $entityManager->getRepository($entityName)->createQueryBuilder('e');
        $queryBuilder->where($queryBuilder->expr()->in('e.' . $idField, $entityIds));
        $currentEntities = $queryBuilder->getQuery()->getResult();

        $resultEntities = array();
        foreach ($currentEntities as $entity) {
            $idValues = $classMetadata->getIdentifierValues($entity);
            $idValue = $idValues[$idField];
            $resultEntities[$idValue] = $entity;
        }

        return $resultEntities;
    }
}

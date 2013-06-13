<?php

namespace Oro\Bundle\SearchBundle\Formatter;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class ResultFormatter
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get array of actual entities
     *
     * Result format: array(
     *     "entityName" => array(
     *         1 => Entity,
     *         2 => Entity,
     *         ...
     *     ),
     *     ...
     * )
     *
     * @param Item[] $elements
     * @return array
     */
    public function getResultEntities(array $elements)
    {
        $entities = array();

        // group elements by type
        foreach ($elements as $element) {
            $entityName = $element->getEntityName();
            $entities[$entityName][] = $element->getRecordId();
        }

        // get actual entities
        foreach ($entities as $entityName => $entityIds) {
            $entities[$entityName] = $this->getEntities($entityName, $entityIds);
        }

        return $entities;
    }

    /**
     * Get list of actual entities in the same order
     *
     * @param Item[] $elements
     * @return array
     */
    public function getOrderedResultEntities(array $elements)
    {
        $entities = $this->getResultEntities($elements);

        // replace elements with entities
        foreach ($elements as $key => $element) {
            $entityName = $element->getEntityName();
            $entityId   = $element->getRecordId();
            if (isset($entities[$entityName][$entityId])) {
                $elements[$key] = $entities[$entityName][$entityId];
            }
        }

        return $elements;
    }

    /**
     * @param  string $entityName
     * @return ClassMetadata
     */
    protected function getEntityMetadata($entityName)
    {
        return $this->entityManager->getMetadataFactory()->getMetadataFor($entityName);
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
     * @param string $entityName
     * @param array $entityIds
     * @return array
     */
    protected function getEntities($entityName, array $entityIds)
    {
        $classMetadata = $this->getEntityMetadata($entityName);
        $idField = $this->getEntityIdentifier($entityName);

        $queryBuilder = $this->entityManager->getRepository($entityName)->createQueryBuilder('e');
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

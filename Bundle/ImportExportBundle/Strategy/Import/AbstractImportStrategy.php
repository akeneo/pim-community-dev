<?php

namespace Oro\Bundle\ImportExportBundle\Strategy\Import;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

abstract class AbstractImportStrategy implements StrategyInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository[]
     */
    protected $repositories;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string $entityClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityClass = $entityClass;
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        if (empty($this->repositories[$entityName])) {
            $this->repositories[$entityName] = $this->managerRegistry->getRepository($entityName);
        }

        return $this->repositories[$entityName];
    }

    /**
     * @return EntityManager
     * @throws LogicException
     */
    protected function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->managerRegistry->getManagerForClass($this->entityClass);
            if (!$this->entityManager) {
                throw new LogicException(
                    sprintf('Can\'t find entity manager for %s', $this->entityClass)
                );
            }
        }

        return $this->entityManager;
    }

    /**
     * @param object $basicEntity
     * @param object $importedEntity
     * @param array $excludedProperties
     */
    protected function importEntity($basicEntity, $importedEntity, array $excludedProperties = array())
    {
        $entityMetadata = $this->getEntityManager()->getClassMetadata($this->entityClass);
        $importedEntityProperties = array_diff(
            array_merge(
                $entityMetadata->getFieldNames(),
                $entityMetadata->getAssociationNames()
            ),
            $excludedProperties
        );

        foreach ($importedEntityProperties as $propertyName) {
            /** @var \ReflectionProperty $reflectionProperty */
            $reflectionProperty = $entityMetadata->getReflectionProperty($propertyName);
            $reflectionProperty->setAccessible(true); // just to make sure
            $importedValue = $reflectionProperty->getValue($importedEntity);
            $reflectionProperty->setValue($basicEntity, $importedValue);
        }
    }
}

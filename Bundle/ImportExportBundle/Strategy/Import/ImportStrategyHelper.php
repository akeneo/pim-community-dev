<?php

namespace Oro\Bundle\ImportExportBundle\Strategy\Import;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;

class ImportStrategyHelper
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param ValidatorInterface $validator
     */
    public function __construct(ManagerRegistry $managerRegistry, ValidatorInterface $validator)
    {
        $this->managerRegistry = $managerRegistry;
        $this->validator = $validator;
    }

    /**
     * @param string $entityClass
     * @return EntityManager
     * @throws LogicException
     */
    protected function getEntityManager($entityClass)
    {
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);
        if (!$entityManager) {
            throw new LogicException(
                sprintf('Can\'t find entity manager for %s', $entityClass)
            );
        }

        return $entityManager;
    }

    /**
     * @param object $basicEntity
     * @param object $importedEntity
     * @param array $excludedProperties
     * @throws InvalidArgumentException
     */
    public function importEntity($basicEntity, $importedEntity, array $excludedProperties = array())
    {
        $basicEntityClass = ClassUtils::getClass($basicEntity);
        if ($basicEntityClass != ClassUtils::getClass($importedEntity)) {
            throw new InvalidArgumentException('Basic and imported entities must be instances of the same class');
        }

        $entityMetadata = $this->getEntityManager($basicEntityClass)->getClassMetadata($basicEntityClass);
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

    /**
     * Validate entity, returns list of errors or null
     *
     * @param object $entity
     * @return array|null
     */
    public function validateEntity($entity)
    {
        $violations = $this->validator->validate($entity);
        if (count($violations)) {
            $errors = array();
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $errors;
        }

        return null;
    }
}

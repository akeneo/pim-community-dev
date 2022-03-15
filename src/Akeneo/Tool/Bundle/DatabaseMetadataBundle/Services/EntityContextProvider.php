<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

class EntityContextProvider
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function mapEntity2LogContext(object $entity): ?array
    {
        $context = [];
        $reflectionClass = new \ReflectionClass($entity);
        try {
            $entityMappingMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        } catch (MappingException $e) {
            return null;
        }

        foreach ($entityMappingMetadata->getIdentifier() as $propertyName) {
            $reflectionProperty = $reflectionClass->getProperty($propertyName);
            $reflectionProperty->setAccessible(true);
            $context[$propertyName] = $reflectionProperty->getValue($entity);
        }
        $entityName = self::convertToSnakeCase($reflectionClass->getShortName());

        return [$entityName => $context];
    }

    protected function computeIdentifierContextFromDoctrineMapping(object $entity): ?array
    {
        $entityMappingMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $reflectionClass = new \ReflectionClass($entity);
        $identifierContext = [];
        foreach ($entityMappingMetadata->getIdentifier() as $propertyName) {
            $reflectionProperty = $reflectionClass->getProperty($propertyName);
            $reflectionProperty->setAccessible(true);
            $identifierContext[$propertyName] = $reflectionProperty->getValue($entity);
        }
        return $identifierContext;
    }

    public static function convertToSnakeCase(string $inputString): string
    {
        preg_match_all('/.[^A-Z]*/', $inputString, $matches);
        return strtolower(implode('_', $matches[0]));
    }
}

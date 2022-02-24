<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\Mapping\MappingException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContextProvider
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

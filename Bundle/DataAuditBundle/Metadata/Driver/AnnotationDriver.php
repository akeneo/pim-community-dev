<?php

namespace Oro\Bundle\DataAuditBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation\Loggable;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation\Versioned;
use Oro\Bundle\DataAuditBundle\Metadata\ClassMetadata;
use Oro\Bundle\DataAuditBundle\Metadata\PropertyMetadata;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;

class AnnotationDriver implements DriverInterface
{
    const LOGGABLE = 'Oro\\Bundle\\DataAuditBundle\\Metadata\\Annotation\\Loggable';

    const VERSIONED = 'Oro\\Bundle\\DataAuditBundle\\Metadata\\Annotation\\Versioned';

    protected $reader;

    protected $em;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param  DoctrineClassMetadata     $doctrineClassMetadata
     * @return null|ClassMetadata
     * @throws \InvalidArgumentException
     */
    public function extendLoadMetadataForClass(DoctrineClassMetadata $doctrineClassMetadata)
    {
        if ($doctrineClassMetadata->isMappedSuperclass
            || !$classMetadata = $this->loadMetadataForClass($doctrineClassMetadata->getReflectionClass())
        ) {
            return null;
        }

        /** @var $property PropertyMetadata */
        foreach ($classMetadata->propertyMetadata as $name => $property) {
            if ($doctrineClassMetadata->isInheritedField($name) ||
                isset($doctrineClassMetadata->associationMappings[$property->name]['inherited'])
            ) {
                unset($classMetadata->propertyMetadata[$name]);
                continue;
            }

            if ($doctrineClassMetadata->isCollectionValuedAssociation($name)) {
                $property->isCollection = true;

                $targetMapping = $doctrineClassMetadata->getAssociationMapping($name);

                if (!method_exists($targetMapping['targetEntity'], $property->method)) {
                    throw new \InvalidArgumentException(sprintf("Method %s in Class %s is not defined. Class must implement a method '__toString' or configure getMethod with Versioned annotation", $property->method, $targetMapping['targetEntity']));
                }
            }
        }

        return $classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($class->getName());

        /** @var Loggable $loggable */
        $loggable = $this->reader->getClassAnnotation($class, self::LOGGABLE);

        foreach ($class->getProperties() as $reflectionProperty) {
            /** @var Versioned $versioned */
            if ($versioned = $this->reader->getPropertyAnnotation($reflectionProperty, self::VERSIONED)) {
                $propertyMetadata         = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
                $propertyMetadata->method = $versioned->method ? $versioned->method : '__toString';

                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        if (count($classMetadata->propertyMetadata) && !$loggable) {
            throw new \InvalidArgumentException("Class must be annoted with Loggable annotation in order to track versioned fields in class - {$classMetadata->name}");
        }

        if (count($classMetadata->propertyMetadata)) {
            return $classMetadata;
        } else {
            return null;
        }
    }
}

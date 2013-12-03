<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

abstract class AbstractOrmQueryConverter extends AbstractQueryConverter
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var ClassMetadata[]
     */
    protected $classMetadataLocalCache;

    /**
     * Constructor
     *
     * @param ManagerRegistry       $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Gets a field data type
     *
     * @param string $className
     * @param string $fieldName
     * @return string
     */
    protected function getFieldType($className, $fieldName)
    {
        return $this->getClassMetadata($className)->getTypeOfField($fieldName);
    }

    /**
     * Returns a metadata for the given entity
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function getClassMetadata($className)
    {
        if (isset($this->classMetadataLocalCache[$className])) {
            return $this->classMetadataLocalCache[$className];
        }

        $classMetadata                             = $this->doctrine
            ->getManagerForClass($className)
            ->getClassMetadata($className);
        $this->classMetadataLocalCache[$className] = $classMetadata;

        return $classMetadata;
    }
}

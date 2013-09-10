<?php

namespace Oro\Bundle\DataAuditBundle\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;

use Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver;

class ExtendMetadataFactory
{
    /**
     * @var AnnotationDriver
     */
    protected $driver;

    /**
     * @param AnnotationDriver $driver
     */
    public function __construct(AnnotationDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param  DoctrineClassMetadata $metadata
     * @return null|ClassMetadata
     */
    public function extendLoadMetadataForClass(DoctrineClassMetadata $metadata)
    {
        return $this->driver->extendLoadMetadataForClass($metadata);
    }
}

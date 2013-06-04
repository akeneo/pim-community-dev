<?php

namespace Oro\Bundle\DataAuditBundle\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver;

class ExtendMetadataFactory
{
    /**
     * @var Driver\AnnotationDriver
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
     * @param  ClassMetadata      $metadata
     * @return null|ClassMetadata
     */
    public function extendLoadMetadataForClass(ClassMetadata $metadata)
    {
        return  $this->driver->extendLoadMetadataForClass($metadata);

    }
}

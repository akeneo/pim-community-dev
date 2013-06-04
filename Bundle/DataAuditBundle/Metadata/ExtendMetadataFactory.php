<?php

namespace Oro\Bundle\DataAuditBundle\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver;

class ExtendMetadataFactory
{
    protected $driver;

    public function __construct(AnnotationDriver $driver)
    {
        $this->driver = $driver;
    }

    public function extendLoadMetadataForClass(ClassMetadata $metadata)
    {
        return  $this->driver->extendLoadMetadataForClass($metadata);

    }
}